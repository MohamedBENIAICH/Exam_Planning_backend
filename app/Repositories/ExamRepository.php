<?php

namespace App\Repositories;

use App\Models\Exam;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class ExamRepository extends BaseRepository
{
    public function __construct(Exam $model)
    {
        parent::__construct($model);
    }

    public function getAllWithRelations()
    {
        return $this->model->with('students')->get();
    }

    public function findWithRelations($id)
    {
        return $this->model->with('students')->findOrFail($id);
    }

    public function getLatestExams($limit = 5)
    {
        return $this->model->with(['students', 'superviseurs', 'professeurs'])
            ->orderBy('id', 'desc')
            ->take($limit)
            ->get();
    }

    public function getUpcomingExams()
    {
        $now = now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        return $this->model->with(['formation', 'filiere', 'module', 'classrooms', 'superviseurs', 'professeurs'])
            ->where(function ($query) use ($today, $currentTime) {
                $query->where('date_examen', '>', $today)
                    ->orWhere(function ($subQuery) use ($today, $currentTime) {
                        $subQuery->where('date_examen', '=', $today)
                            ->where('heure_fin', '>', $currentTime);
                    });
            })
            ->orderBy('date_examen', 'asc')
            ->orderBy('heure_debut', 'asc')
            ->get();
    }

    public function getPassedExams()
    {
        $now = now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        return $this->model->with(['formation', 'filiere', 'module', 'classrooms', 'superviseurs', 'professeurs'])
            ->where(function ($query) use ($today, $currentTime) {
                $query->where('date_examen', '<', $today)
                    ->orWhere(function ($subQuery) use ($today, $currentTime) {
                        $subQuery->where('date_examen', '=', $today)
                            ->where('heure_fin', '<=', $currentTime);
                    });
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getExamsWithNames()
    {
        return $this->model->with(['formation', 'filiere', 'module', 'classrooms', 'superviseurs', 'professeurs'])->get();
    }

    public function countPassedExams()
    {
        $today = now()->toDateString();
        return $this->model->where('date_examen', '<', $today)->count();
    }

    public function countUpcomingExams()
    {
        $today = now()->toDateString();
        return $this->model->where('date_examen', '>=', $today)->count();
    }

    public function createWithRelations(array $examData, array $classroomIds = [], array $studentIds = [], array $professeurIds = [])
    {
        DB::beginTransaction();
        try {
            $exam = $this->create($examData);

            if (!empty($classroomIds)) {
                $exam->classrooms()->sync($classroomIds);
            }

            if (!empty($studentIds)) {
                $exam->students()->sync($studentIds);
            }

            if (!empty($professeurIds)) {
                $exam->professeurs()->sync($professeurIds);
            }

            DB::commit();
            return $exam->load(['students', 'module', 'classrooms', 'professeurs']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateWithRelations($id, array $examData, array $classroomIds = [], array $studentIds = [], array $superviseurIds = [])
    {
        DB::beginTransaction();
        try {
            $exam = $this->update($id, $examData);

            if (!empty($classroomIds)) {
                $exam->classrooms()->sync($classroomIds);
            }

            if (!empty($studentIds)) {
                $exam->students()->sync($studentIds);
            }

            if (!empty($superviseurIds)) {
                $exam->superviseurs()->sync($superviseurIds);
            }

            DB::commit();
            return $exam->load(['students', 'superviseurs', 'professeurs']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
