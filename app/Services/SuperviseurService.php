<?php

namespace App\Services;

use App\Repositories\SuperviseurRepository;

class SuperviseurService
{
    protected $superviseurRepository;

    public function __construct(SuperviseurRepository $superviseurRepository)
    {
        $this->superviseurRepository = $superviseurRepository;
    }

    public function getAllSuperviseurs()
    {
        return $this->superviseurRepository->all();
    }

    public function getSuperviseurById($id)
    {
        return $this->superviseurRepository->findOrFail($id);
    }

    public function createSuperviseur(array $data)
    {
        return $this->superviseurRepository->create($data);
    }

    public function updateSuperviseur($id, array $data)
    {
        return $this->superviseurRepository->update($id, $data);
    }

    public function deleteSuperviseur($id)
    {
        return $this->superviseurRepository->delete($id);
    }

    public function getSuperviseursByService($service)
    {
        return $this->superviseurRepository->getByService($service);
    }

    public function getAllServices()
    {
        return $this->superviseurRepository->getAllServices();
    }

    public function findSuperviseurByName($prenom, $nom)
    {
        return $this->superviseurRepository->findByName($prenom, $nom);
    }
}
