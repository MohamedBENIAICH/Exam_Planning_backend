<?php

namespace App\Repositories;

use App\Models\Professeur;
use App\Repositories\BaseRepository;

class ProfesseurRepository extends BaseRepository
{
    public function __construct(Professeur $model)
    {
        parent::__construct($model);
    }

    public function getByDepartement($departement)
    {
        return $this->model->where('departement', $departement)
            ->select('id', 'nom', 'prenom', 'email', 'departement')
            ->get();
    }

    public function getAllDepartements()
    {
        return $this->model->select('departement')
            ->distinct()
            ->orderBy('departement')
            ->pluck('departement');
    }

    public function findByName($prenom, $nom)
    {
        return $this->model->where('prenom', $prenom)
            ->where('nom', $nom)
            ->first();
    }

    public function firstOrCreate(array $attributes, array $values = [])
    {
        return $this->model->firstOrCreate($attributes, $values);
    }
}
