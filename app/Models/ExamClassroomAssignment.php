<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamClassroomAssignment extends Model
{
    protected $fillable = [
        'exam_id',
        'classroom_id',
        'student_id',
        'seat_number'
    ];

    /**
     * Get the exam that owns the assignment.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the classroom that owns the assignment.
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Get the student that owns the assignment.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
