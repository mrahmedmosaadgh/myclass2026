<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'h_r_id'];

    public function hr()
    {
        return $this->belongsTo(HR::class, 'h_r_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    public function parents()
    {
        return $this->hasMany(StudentParent::class);
    }

    public function stages()
    {
        return $this->hasMany(Stage::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function sections()
    {
        return $this->hasMany(SchoolSection::class);
    }
}

