<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_filiere';

    protected $fillable = [
        'filiere_intitule',
        'id_departement',
        'id_formation'
    ];

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'id_departement', 'id_departement');
    }

    public function formation()
    {
        return $this->belongsTo(Formation::class, 'id_formation', 'id_formation');
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'filiere_module', 'id_filiere', 'id_module')
            ->withTimestamps();
    }
}
