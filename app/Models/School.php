<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'section',
        'section_ar',
        'h_r_id',
        'data',
        'is_active',
        'active_academic_year_id',
        'active_semester_id',
        'weekly_plan_settings',
        'resolved_by',
        'resolved_at',
        'week_number',
        'locked',
        'weekly_settings'
    ];

    protected $casts = [
        'data' => 'array',
        'weekly_plan_settings' => 'array',
        'weekly_settings' => 'array',
        'is_active' => 'boolean',
        'week_number' => 'integer',
        'locked' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the current academic year ID for the school.
     * Alias of active_academic_year_id for consistency.
     */
    public function getCurrentAcademicYearIdAttribute(): ?int
    {
        return $this->active_academic_year_id;
    }

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

    public function academic_years()
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function activeAcademicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'active_academic_year_id');
    }

    public function activeSemester()
    {
        return $this->belongsTo(Semester::class, 'active_semester_id');
    }
    
   
    
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    
    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get the branding data from the JSON data column
     */
    public function getBrandingAttribute()
    {
        $data = $this->data ?? [];
        return $data['branding'] ?? [
            'school_slug' => null,
            'logo_path' => null,
            'background_path' => null,
            'school_name_en' => $this->name,
            'school_name_ar' => $this->name_ar,
            'colors' => [
                'primary' => '#6366f1',
                'secondary' => '#8b5cf6',
                'accent' => '#ec4899'
            ],
            'login_page_settings' => [
                'show_particles' => true,
                'animation_style' => 'fade',
                'card_style' => 'glassmorphism'
            ]
        ];
    }

    /**
     * Set the branding data in the JSON data column
     */
    public function setBrandingAttribute($value)
    {
        $data = $this->data ?? [];
        $data['branding'] = $value;
        $this->attributes['data'] = json_encode($data);
    }

    /**
     * Get the school slug for URL routing
     */
    public function getSchoolSlugAttribute()
    {
        $branding = $this->branding;
        
        // If slug exists in branding, use it
        if (!empty($branding['school_slug'])) {
            return $branding['school_slug'];
        }
        
        // Otherwise, generate from school name
        return \Illuminate\Support\Str::slug($this->name);
    }

    /**
     * Update branding data
     */
    public function updateBranding(array $brandingData)
    {
        $currentBranding = $this->branding;
        $updatedBranding = array_merge($currentBranding, $brandingData);
        
        $data = $this->data ?? [];
        $data['branding'] = $updatedBranding;
        $this->data = $data;
        $this->save();
        
        return $this;
    }

    /**
     * Get the full URL for the logo
     */
    public function getLogoUrlAttribute()
    {
        $branding = $this->branding;
        if (!empty($branding['logo_path'])) {
            // Check if it's a direct public upload
            if (strpos($branding['logo_path'], 'uploads/') === 0) {
                return asset($branding['logo_path']);
            }
            // Fallback for legacy storage
            return asset('storage/' . $branding['logo_path']);
        }
        return null;
    }

    /**
     * Get the full URL for the background image
     */
    public function getBackgroundUrlAttribute()
    {
        $branding = $this->branding;
        if (!empty($branding['background_path'])) {
            // Check if it's a direct public upload
            if (strpos($branding['background_path'], 'uploads/') === 0) {
                return asset($branding['background_path']);
            }
            // Fallback for legacy storage
            return asset('storage/' . $branding['background_path']);
        }
        return null;
    }
}