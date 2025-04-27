<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_module';

    protected $fillable = [
        'module_intitule',
        'semestre'
    ];

    public function filieres()
    {
        return $this->belongsToMany(Filiere::class, 'filiere_module', 'id_module', 'id_filiere')
            ->withTimestamps();
    }
}
