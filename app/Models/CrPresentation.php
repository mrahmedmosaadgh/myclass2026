<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CrPresentation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cr_presentations';

    protected $fillable = [
        'title',
        'description',
        'slug',
        'cr_presentation_category_id',
        'user_id',
        'school_id',
        'classroom_id',
        'slides',
        'current_slide_index',
        'use_phases',
        'has_initialized_phases',
        'metadata',
        'status',
        'is_public',
        'is_template',
    ];

    protected $casts = [
        'slides' => 'array',
        'metadata' => 'array',
        'current_slide_index' => 'integer',
        'use_phases' => 'boolean',
        'has_initialized_phases' => 'boolean',
        'is_public' => 'boolean',
        'is_template' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($presentation) {
            if (empty($presentation->slug)) {
                $presentation->slug = static::generateUniqueSlug($presentation->title);
            }
        });

        static::updating(function ($presentation) {
            if ($presentation->isDirty('title')) {
                $presentation->slug = static::generateUniqueSlug($presentation->title);
            }
        });
    }

    protected static function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(CrPresentationCategory::class, 'cr_presentation_category_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('cr_presentation_category_id', $categoryId);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%");
        });
    }

    public function canBeAccessedBy($user)
    {
        if (!$user) {
            return false;
        }

        if ($this->user_id === $user->id) {
            return true;
        }

        if ($this->is_public && $this->status === 'published') {
            return true;
        }

        if ($user->school_id && $this->school_id === $user->school_id) {
            return true;
        }

        return false;
    }
}
