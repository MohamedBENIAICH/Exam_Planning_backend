<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formation extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_formation';

    protected $fillable = [
        'formation_intitule'
    ];

    public function filieres()
    {
        return $this->hasMany(Filiere::class, 'id_formation', 'id_formation');
    }
}
