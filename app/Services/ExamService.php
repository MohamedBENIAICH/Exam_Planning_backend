<?php

namespace App\Services;

use App\Repositories\ExamRepository;
use App\Repositories\StudentRepository;
use App\Repositories\ProfesseurRepository;
use App\Repositories\SuperviseurRepository;
use App\Models\Formation;
use App\Models\Filiere;
use App\Models\Module;
use App\Models\ClassroomExamSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExamSurveillanceNotification;

class ExamService
{
    protected $examRepository;
    protected $studentRepository;
    protected $professeurRepository;
    protected $superviseurRepository;
    protected $examNotificationService;

    public function __construct(
        ExamRepository $examRepository,
        StudentRepository $studentRepository,
        ProfesseurRepository $professeurRepository,
        SuperviseurRepository $superviseurRepository
    ) {
        $this->examRepository = $examRepository;
        $this->studentRepository = $studentRepository;
        $this->professeurRepository = $professeurRepository;
        $this->superviseurRepository = $superviseurRepository;
    }

    public function setExamNotificationService($service)
    {
        $this->examNotificationService = $service;
    }

    public function getAllExams()
    {
        return $this->examRepository->getAllWithRelations();
    }

    public function getExamById($id)
    {
        return $this->examRepository->findWithRelations($id);
    }

    public function getExamCount()
    {
        return $this->examRepository->count();
    }

    public function getLatestExams($limit = 5)
    {
        return $this->examRepository->getLatestExams($limit);
    }

    public function getUpcomingExams()
    {
        return $this->examRepository->getUpcomingExams();
    }

    public function getPassedExams()
    {
        return $this->examRepository->getPassedExams();
    }

    public function getExamsWithNames()
    {
        return $this->examRepository->getExamsWithNames();
    }

    public function countPassedExams()
    {
        return $this->examRepository->countPassedExams();
    }

    public function countUpcomingExams()
    {
        return $this->examRepository->countUpcomingExams();
    }

    public function createExam(array $data)
    {
        DB::beginTransaction();
        try {
            $examData = [
                'formation' => $data['formation'],
                'filiere' => $data['filiere'],
                'module_id' => $data['module'],
                'semestre' => $data['semestre'],
                'date_examen' => $data['date_examen'],
                'heure_debut' => $data['heure_debut'],
                'heure_fin' => $data['heure_fin'],
                'locaux' => $data['locaux'],
                'superviseurs' => $data['superviseurs'] ?? null,
                'professeurs' => $data['professeurs']
            ];

            $exam = $this->examRepository->create($examData);

            if (isset($data['professeurs'])) {
                $professeurIds = $this->processProfesseurs($data['professeurs'], $data['filiere']);
                $exam->professeurs()->sync($professeurIds);
                $this->logSync('professors', $professeurIds, $exam->id);
            }

            if (isset($data['classroom_ids']) && !empty($data['classroom_ids'])) {
                $classroomIds = array_map('intval', $data['classroom_ids']);
                $exam->classrooms()->sync($classroomIds);
                $this->createClassroomSchedules($classroomIds, $exam->id, $data);
                $this->logSync('classrooms', $classroomIds, $exam->id);
            }

            if (isset($data['students']) && is_array($data['students'])) {
                $studentIds = $this->processStudents($data['students']);
                $exam->students()->sync($studentIds);
                $this->logSync('students', $studentIds, $exam->id);
            }

            DB::commit();

            $exam->load(['students', 'module', 'classrooms']);

            $this->sendSupervisorNotifications($exam, $data['superviseurs'] ?? null);
            $this->sendProfessorNotifications($exam, $data['professeurs'] ?? null);

            return $exam;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateExam($id, array $data)
    {
        DB::beginTransaction();
        try {
            $examData = [
                'formation' => $data['formation'],
                'filiere' => $data['filiere'],
                'module_id' => $data['module'],
                'semestre' => $data['semestre'],
                'date_examen' => $data['date_examen'],
                'heure_debut' => $data['heure_debut'],
                'heure_fin' => $data['heure_fin'],
                'locaux' => $data['locaux'],
                'superviseurs' => $data['superviseurs'] ?? null,
                'professeurs' => $data['professeurs']
            ];

            $exam = $this->examRepository->update($id, $examData);

            if (isset($data['classroom_ids'])) {
                $classroomIds = array_map('intval', $data['classroom_ids']);
                $exam->classrooms()->sync($classroomIds);
                $this->createClassroomSchedules($classroomIds, $exam->id, $data);
                $this->logSync('classrooms', $classroomIds, $exam->id);
            }

            if (isset($data['superviseur_ids'])) {
                $superviseurIds = array_map('intval', $data['superviseur_ids']);
                $exam->superviseurs()->sync($superviseurIds);
                $this->logSync('supervisors', $superviseurIds, $exam->id);
            }

            if (isset($data['students']) && is_array($data['students'])) {
                $studentIds = $this->processStudents($data['students']);
                $exam->students()->sync($studentIds);
            }

            DB::commit();

            $exam->load(['students', 'superviseurs']);

            if ($this->examNotificationService) {
                $this->examNotificationService->sendSupervisorNotifications($exam);
                $this->examNotificationService->sendUpdateNotifications($exam);
            }

            return $exam;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($id, $e);
            throw $e;
        }
    }

    public function deleteExam($id)
    {
        $exam = $this->examRepository->findOrFail($id);
        return $exam->delete();
    }

    public function cancelExam($id)
    {
        $exam = $this->examRepository->with(['students', 'superviseurs', 'professeurs', 'module'])->findOrFail($id);

        $now = now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        $isPassed = ($exam->date_examen < $today) ||
            ($exam->date_examen->format('Y-m-d') === $today && $exam->heure_fin->format('H:i:s') <= $currentTime);

        if ($isPassed) {
            $exam->delete();
            return ['message' => 'Exam (déjà passé) supprimé sans notification.'];
        }

        if ($this->examNotificationService) {
            $this->examNotificationService->sendCancellationNotifications($exam);
        }
        $exam->delete();

        return ['message' => 'Exam annulé et supprimé avec succès'];
    }

    public function transformExamWithNames($exam)
    {
        $formation = Formation::find($exam->formation);
        $filiere = Filiere::find($exam->filiere);
        $module = Module::find($exam->module_id);

        $heure_debut = \Carbon\Carbon::parse($exam->heure_debut);
        $heure_fin = \Carbon\Carbon::parse($exam->heure_fin);
        $duree = $heure_debut->diffInMinutes($heure_fin);

        $classroomNames = $exam->classrooms->pluck('nom_du_local')->implode(', ');
        $locaux = $classroomNames ?: $exam->locaux;

        $superviseursNames = $this->getSuperviseurNames($exam);
        $professeursNames = $this->getProfesseurNames($exam);

        return [
            'id' => $exam->id,
            'cycle' => $formation ? $formation->formation_intitule : null,
            'formation_name' => $formation ? $formation->formation_intitule : null,
            'filiere_name' => $filiere ? $filiere->filiere_intitule : null,
            'module_name' => $module ? $module->module_intitule : null,
            'semestre' => $exam->semestre,
            'date_examen' => date('Y-m-d', strtotime($exam->date_examen)),
            'heure_debut' => date('H:i', strtotime($exam->heure_debut)),
            'heure_fin' => date('H:i', strtotime($exam->heure_fin)),
            'duree' => $duree,
            'locaux' => $locaux,
            'superviseurs' => $superviseursNames,
            'professeurs' => $professeursNames,
            'created_at' => $exam->created_at ? $exam->created_at->toISOString() : null,
            'updated_at' => $exam->updated_at ? $exam->updated_at->toISOString() : null
        ];
    }

    private function processProfesseurs($professeurs, $filiere)
    {
        $professeurNames = explode(',', $professeurs);
        $professeurIds = [];

        foreach ($professeurNames as $name) {
            $nameParts = explode(' ', trim($name));
            if (count($nameParts) >= 2) {
                $professeur = $this->professeurRepository->findByName($nameParts[0], $nameParts[1]);

                if (!$professeur) {
                    $professeur = $this->professeurRepository->create([
                        'nom' => $nameParts[1],
                        'prenom' => $nameParts[0],
                        'departement' => $filiere,
                        'email' => strtolower($nameParts[0] . '.' . $nameParts[1] . '@example.com')
                    ]);
                }

                $professeurIds[] = $professeur->id;
            }
        }

        return $professeurIds;
    }

    private function processStudents($students)
    {
        $studentIds = [];
        foreach ($students as $studentData) {
            $student = $this->studentRepository->findByNumeroEtudiant($studentData['studentId']);

            if (!$student) {
                $student = $this->studentRepository->create([
                    'nom' => $studentData['lastName'],
                    'prenom' => $studentData['firstName'],
                    'numero_etudiant' => $studentData['studentId'],
                    'email' => $studentData['email'],
                    'filiere' => $studentData['program'],
                    'niveau' => 'L3'
                ]);
            }

            $studentIds[] = $student->id;
        }

        return $studentIds;
    }

    private function createClassroomSchedules($classroomIds, $examId, $data)
    {
        foreach ($classroomIds as $classroomId) {
            ClassroomExamSchedule::create([
                'classroom_id' => $classroomId,
                'exam_id' => $examId,
                'date_examen' => $data['date_examen'],
                'heure_debut' => $data['heure_debut'],
                'heure_fin' => $data['heure_fin'],
            ]);
        }
    }

    private function sendSupervisorNotifications($exam, $superviseurs)
    {
        if (!$superviseurs) {
            return;
        }

        $superviseurNames = explode(',', $superviseurs);
        foreach ($superviseurNames as $name) {
            $nameParts = explode(' ', trim($name));
            if (count($nameParts) >= 2) {
                $superviseur = $this->superviseurRepository->findByName($nameParts[0], $nameParts[1]);

                if ($superviseur && $superviseur->email) {
                    try {
                        Mail::to($superviseur->email)->send(new ExamSurveillanceNotification($exam, $name));
                        Log::info("Notification envoyée au superviseur: {$name} ({$superviseur->email})");
                    } catch (\Exception $e) {
                        Log::error("Erreur d'envoi d'email au superviseur {$name}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    private function sendProfessorNotifications($exam, $professeurs)
    {
        if (!$professeurs) {
            return;
        }

        $professeurNames = explode(',', $professeurs);
        foreach ($professeurNames as $name) {
            $nameParts = explode(' ', trim($name));
            if (count($nameParts) >= 2) {
                $professeur = $this->professeurRepository->findByName($nameParts[0], $nameParts[1]);

                if ($professeur && $professeur->email) {
                    try {
                        Mail::to($professeur->email)->send(new ExamSurveillanceNotification($exam, $name));
                        Log::info("Notification envoyée au professeur: {$name} ({$professeur->email})");
                    } catch (\Exception $e) {
                        Log::error("Erreur d'envoi d'email au professeur {$name}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    private function getSuperviseurNames($exam)
    {
        if ($exam->superviseurs && is_object($exam->superviseurs) && $exam->superviseurs->count() > 0) {
            return $exam->superviseurs->map(function ($superviseur) {
                return $superviseur->prenom . ' ' . $superviseur->nom;
            })->implode(', ');
        } elseif (is_string($exam->superviseurs) && !empty($exam->superviseurs)) {
            return $exam->superviseurs;
        }
        return '';
    }

    private function getProfesseurNames($exam)
    {
        if ($exam->professeurs && is_object($exam->professeurs) && $exam->professeurs->count() > 0) {
            return $exam->professeurs->map(function ($professeur) {
                return $professeur->prenom . ' ' . $professeur->nom;
            })->implode(', ');
        } elseif (is_string($exam->professeurs) && !empty($exam->professeurs)) {
            return $exam->professeurs;
        }
        return '';
    }

    private function logSync($type, $ids, $examId)
    {
        DB::table('logs')->insert([
            'message' => "Synced {$type} " . implode(', ', $ids) . " with exam {$examId}",
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function logError($id, $e)
    {
        DB::table('logs')->insert([
            'message' => "Error updating exam {$id}: " . $e->getMessage(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
