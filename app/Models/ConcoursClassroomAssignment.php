<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConcoursClassroomAssignment extends Model
{
    use HasFactory;

    protected $table = 'concours_classroom_assignments';

    protected $fillable = [
        'concours_id',
        'classroom_id',
        'candidat_id',
        'seat_number',
    ];

    protected $casts = [
        'seat_number' => 'integer',
    ];

    /**
     * Get the concours that owns the assignment.
     */
    public function concours()
    {
        return $this->belongsTo(Concours::class);
    }

    /**
     * Get the classroom that owns the assignment.
     */
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Get the candidat that owns the assignment.
     */
    public function candidat()
    {
        return $this->belongsTo(Candidat::class);
    }
}
