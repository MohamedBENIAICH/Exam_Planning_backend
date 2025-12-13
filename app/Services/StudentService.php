<?php

namespace App\Services;

use App\Repositories\StudentRepository;
use App\Repositories\ExamRepository;

class StudentService
{
    protected $studentRepository;
    protected $examRepository;

    public function __construct(StudentRepository $studentRepository, ExamRepository $examRepository)
    {
        $this->studentRepository = $studentRepository;
        $this->examRepository = $examRepository;
    }

    public function getAllStudents()
    {
        return $this->studentRepository->all();
    }

    public function getStudentById($id)
    {
        return $this->studentRepository->findOrFail($id);
    }

    public function createStudent(array $data)
    {
        return $this->studentRepository->create($data);
    }

    public function updateStudent($id, array $data)
    {
        return $this->studentRepository->update($id, $data);
    }

    public function deleteStudent($id)
    {
        return $this->studentRepository->delete($id);
    }

    public function getStudentCount()
    {
        return $this->studentRepository->count();
    }

    public function getStudentsByExamId($examId)
    {
        $exam = $this->examRepository->findOrFail($examId);
        return $exam->students;
    }

    public function findOrCreateStudent(array $studentData)
    {
        $student = $this->studentRepository->findByNumeroEtudiant($studentData['studentId']);

        if (!$student) {
            $student = $this->studentRepository->create([
                'nom' => $studentData['lastName'],
                'prenom' => $studentData['firstName'],
                'numero_etudiant' => $studentData['studentId'],
                'email' => $studentData['email'],
                'filiere' => $studentData['program'],
                'niveau' => $studentData['niveau'] ?? 'L3'
            ]);
        }

        return $student;
    }
}
