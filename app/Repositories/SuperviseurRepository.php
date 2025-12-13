<?php

namespace App\Repositories;

use App\Models\Superviseur;
use App\Repositories\BaseRepository;

class SuperviseurRepository extends BaseRepository
{
    public function __construct(Superviseur $model)
    {
        parent::__construct($model);
    }

    public function getByService($service)
    {
        return $this->model->where('service', $service)
            ->select('id', 'service', 'nom', 'prenom', 'type')
            ->get();
    }

    public function getAllServices()
    {
        return $this->model->select('service')
            ->distinct()
            ->orderBy('service')
            ->pluck('service');
    }

    public function findByName($prenom, $nom)
    {
        return $this->model->where('prenom', $prenom)
            ->where('nom', $nom)
            ->first();
    }
}
