<?php

namespace App\Services;

use App\Repositories\ProfesseurRepository;

class ProfesseurService
{
    protected $professeurRepository;

    public function __construct(ProfesseurRepository $professeurRepository)
    {
        $this->professeurRepository = $professeurRepository;
    }

    public function getAllProfesseurs()
    {
        return $this->professeurRepository->all();
    }

    public function getProfesseurById($id)
    {
        return $this->professeurRepository->findOrFail($id);
    }

    public function createProfesseur(array $data)
    {
        return $this->professeurRepository->create($data);
    }

    public function updateProfesseur($id, array $data)
    {
        return $this->professeurRepository->update($id, $data);
    }

    public function deleteProfesseur($id)
    {
        return $this->professeurRepository->delete($id);
    }

    public function getProfesseursByDepartement($departement)
    {
        return $this->professeurRepository->getByDepartement($departement);
    }

    public function getAllDepartements()
    {
        return $this->professeurRepository->getAllDepartements();
    }

    public function getProfesseurCount()
    {
        return $this->professeurRepository->count();
    }

    public function findProfesseurByName($prenom, $nom)
    {
        return $this->professeurRepository->findByName($prenom, $nom);
    }

    public function findOrCreateProfesseur($prenom, $nom, $departement = null)
    {
        $professeur = $this->professeurRepository->findByName($prenom, $nom);

        if (!$professeur) {
            $professeur = $this->professeurRepository->create([
                'nom' => $nom,
                'prenom' => $prenom,
                'departement' => $departement,
                'email' => strtolower($prenom . '.' . $nom . '@example.com')
            ]);
        }

        return $professeur;
    }
}
