<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
    ];

    /**
     * Relation : Un type de permission peut être lié à plusieurs accès.
     */
    public function acces()
    {
        return $this->hasMany(Acces::class);
    }
}
