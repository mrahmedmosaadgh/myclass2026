<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class StudentParent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'user_id',
        'school_id',
        'data',
        'report'
    ];

    protected $casts = [
        'data' => 'json',
        'report' => 'boolean'
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($teacher) {
            // Generate a unique p_id if not provided
            if (empty($teacher->p_id)) {
                do {
                    $uniqueId = 'p' . strtolower(Str::random(4, 'abcdefghijklmnopqrstuvwxyz')) . rand(1000, 9999);
                } while (self::where('p_id', $uniqueId)->exists());

                $teacher->p_id = $uniqueId;
            }







            // Check if a user with the given email already exists
            $user = User::where('email', $teacher->p_id)->first();
            // $user = User::where('email', $teacher->email)->first();

            if (!$user) {
                // Create a new user if not exists
                $user = User::create([
                    'name' => $teacher->name,
                    'email' => $teacher->p_id,
                    'role' =>  'parent' ,

                    // 'email' => $teacher->email,
                    // 'password' => bcrypt(Str::random(10)), // Generate a random password
                    'password' => bcrypt('12345678'), // Generate a random password
                ]);
            }

            $teacher->user_id = $user->id;
        });
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }
}
