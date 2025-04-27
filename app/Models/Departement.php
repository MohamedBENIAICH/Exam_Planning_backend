<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    use HasFactory;

    protected $table = 'departements';
    protected $primaryKey = 'id_departement';
    public $timestamps = true;

    protected $fillable = [
        'nom_departement'
    ];

    public function filieres()
    {
        return $this->hasMany(Filiere::class, 'id_departement', 'id_departement');
    }
}
