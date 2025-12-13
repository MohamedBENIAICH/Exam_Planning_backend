<?php

namespace App\Repositories;

use App\Models\Classroom;
use App\Models\ClassroomExamSchedule;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class ClassroomRepository extends BaseRepository
{
    public function __construct(Classroom $model)
    {
        parent::__construct($model);
    }

    public function getAllOrderedByCreatedAt()
    {
        return $this->model->orderBy('created_at', 'desc')->get();
    }

    public function findByName($name)
    {
        return $this->model->where('nom_du_local', $name)->first();
    }

    public function getByDepartment($department)
    {
        return $this->model->where('departement', $department)
            ->whereRaw('LOWER(nom_du_local) NOT LIKE ?', ['amphi%'])
            ->orderBy('nom_du_local')
            ->get();
    }

    public function getAmphitheaters()
    {
        return $this->model->whereRaw('LOWER(nom_du_local) LIKE ?', ['amphi%'])->get();
    }

    public function getScheduledClassroomsByDateTime($dateExamen, $heureDebut, $heureFin, $departement)
    {
        $heureDebut = date('H:i:s', strtotime($heureDebut));
        $heureFin = date('H:i:s', strtotime($heureFin));

        return DB::table('classroom_exam_schedule')
            ->join('classrooms', 'classroom_exam_schedule.classroom_id', '=', 'classrooms.id')
            ->where('date_examen', $dateExamen)
            ->where('classrooms.departement', $departement)
            ->where(function ($q) use ($heureDebut, $heureFin) {
                $q->where(function ($subq) use ($heureDebut, $heureFin) {
                    $subq->where('heure_debut', '<=', $heureDebut)
                        ->where('heure_fin', '>', $heureDebut);
                })->orWhere(function ($subq) use ($heureDebut, $heureFin) {
                    $subq->where('heure_debut', '<', $heureFin)
                        ->where('heure_fin', '>=', $heureFin);
                })->orWhere(function ($subq) use ($heureDebut, $heureFin) {
                    $subq->where('heure_debut', '>=', $heureDebut)
                        ->where('heure_fin', '<=', $heureFin);
                });
            })
            ->select('classrooms.*', 'classroom_exam_schedule.date_examen', 'classroom_exam_schedule.heure_debut', 'classroom_exam_schedule.heure_fin')
            ->get();
    }

    public function getClassroomsNotInList(array $classroomIds)
    {
        if (empty($classroomIds)) {
            return $this->all();
        }
        return $this->model->whereNotIn('id', $classroomIds)->get();
    }

    public function getAvailableCount($date, $heureDebut, $heureFin)
    {
        $sallesOccupees = ClassroomExamSchedule::where('date_examen', $date)
            ->where(function ($query) use ($heureDebut, $heureFin) {
                $query->where(function ($q) use ($heureDebut, $heureFin) {
                    $q->where('heure_debut', '<=', $heureDebut)
                        ->where('heure_fin', '>', $heureDebut);
                })->orWhere(function ($q) use ($heureDebut, $heureFin) {
                    $q->where('heure_debut', '<', $heureFin)
                        ->where('heure_fin', '>=', $heureFin);
                })->orWhere(function ($q) use ($heureDebut, $heureFin) {
                    $q->where('heure_debut', '>=', $heureDebut)
                        ->where('heure_fin', '<=', $heureFin);
                });
            })
            ->pluck('classroom_id');

        return $this->model->whereNotIn('id', $sallesOccupees)->count();
    }

    public function checkAvailability($classroomId, $dateExamen, $heureDebut, $heureFin)
    {
        return !ClassroomExamSchedule::where('classroom_id', $classroomId)
            ->where('date_examen', $dateExamen)
            ->where(function ($query) use ($heureDebut, $heureFin) {
                $query->where(function ($q) use ($heureDebut, $heureFin) {
                    $q->where('heure_debut', '<=', $heureDebut)
                        ->where('heure_fin', '>', $heureDebut);
                })->orWhere(function ($q) use ($heureDebut, $heureFin) {
                    $q->where('heure_debut', '<', $heureFin)
                        ->where('heure_fin', '>=', $heureFin);
                })->orWhere(function ($q) use ($heureDebut, $heureFin) {
                    $q->where('heure_debut', '>=', $heureDebut)
                        ->where('heure_fin', '<=', $heureFin);
                });
            })
            ->exists();
    }
}
