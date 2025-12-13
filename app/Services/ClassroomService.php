<?php

namespace App\Services;

use App\Repositories\ClassroomRepository;
use App\Models\ClassroomExamSchedule;

class ClassroomService
{
    protected $classroomRepository;

    public function __construct(ClassroomRepository $classroomRepository)
    {
        $this->classroomRepository = $classroomRepository;
    }

    public function getAllClassrooms()
    {
        return $this->classroomRepository->getAllOrderedByCreatedAt();
    }

    public function getClassroomById($id)
    {
        return $this->classroomRepository->findOrFail($id);
    }

    public function createClassroom(array $data)
    {
        return $this->classroomRepository->create($data);
    }

    public function updateClassroom($id, array $data)
    {
        return $this->classroomRepository->update($id, $data);
    }

    public function deleteClassroom($id)
    {
        return $this->classroomRepository->delete($id);
    }

    public function getClassroomCount()
    {
        return $this->classroomRepository->count();
    }

    public function getAvailableClassrooms()
    {
        return $this->classroomRepository->all();
    }

    public function getClassroomByName($name)
    {
        return $this->classroomRepository->findByName($name);
    }

    public function getClassroomsByDepartment($department)
    {
        return $this->classroomRepository->getByDepartment($department);
    }

    public function getAmphitheaters()
    {
        return $this->classroomRepository->getAmphitheaters();
    }

    public function getScheduledClassroomsByDateTime($dateExamen, $heureDebut, $heureFin, $departement)
    {
        return $this->classroomRepository->getScheduledClassroomsByDateTime($dateExamen, $heureDebut, $heureFin, $departement);
    }

    public function getClassroomsNotInList(array $classroomIds)
    {
        return $this->classroomRepository->getClassroomsNotInList($classroomIds);
    }

    public function getAvailableCount($date, $heureDebut, $heureFin)
    {
        return $this->classroomRepository->getAvailableCount($date, $heureDebut, $heureFin);
    }

    public function updateDisponibilite($id, $disponible)
    {
        return $this->classroomRepository->update($id, ['disponible' => $disponible]);
    }

    public function scheduleExam(array $data)
    {
        $isAvailable = $this->classroomRepository->checkAvailability(
            $data['classroom_id'],
            $data['date_examen'],
            $data['heure_debut'],
            $data['heure_fin']
        );

        if (!$isAvailable) {
            throw new \Exception('Classroom is not available for the specified time slot');
        }

        return ClassroomExamSchedule::create([
            'classroom_id' => $data['classroom_id'],
            'exam_id' => $data['exam_id'],
            'date_examen' => $data['date_examen'],
            'heure_debut' => $data['heure_debut'],
            'heure_fin' => $data['heure_fin'],
        ]);
    }
}
