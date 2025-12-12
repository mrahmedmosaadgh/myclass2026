<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassroomRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id','year_id','period_code','teacher_id','classroom_id','student_id',
        'subject_id','attend','book','homework','out_classroom','out_classroom_notes',
        'turn1','turn2','turn3','plus','minus','total','date','time','points_details','notes'
    ];

    public function teacher() { return $this->belongsTo(Teacher::class); }
    public function classroom() { return $this->belongsTo(Classroom::class); }
    public function student() { return $this->belongsTo(Student::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
}
