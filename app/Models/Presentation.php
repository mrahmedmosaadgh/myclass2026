<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Presentation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'slug',
        'category_id',
        'user_id',
        'school_id',
        'classroom_id',
        'slides_file_path',
        'file_size_bytes',
        'current_slide_index',
        'use_phases',
        'has_initialized_phases',
        'metadata',
        'status',
        'is_public',
        'is_template'
    ];

    protected $casts = [
        'metadata' => 'array',
        'use_phases' => 'boolean',
        'has_initialized_phases' => 'boolean',
        'is_public' => 'boolean',
        'is_template' => 'boolean',
        'current_slide_index' => 'integer',
        'file_size_bytes' => 'integer'
    ];

    protected $dates = [
        'deleted_at'
    ];

    // Boot method for automatic slug generation
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($presentation) {
            $presentation->slug = static::generateUniqueSlug($presentation->title);
        });

        static::updating(function ($presentation) {
            if ($presentation->isDirty('title')) {
                $presentation->slug = static::generateUniqueSlug($presentation->title);
            }
        });
    }

    /**
     * Generate unique slug for presentation
     */
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

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(CrPresentationCategory::class, 'category_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function backups()
    {
        return $this->hasMany(PresentationBackup::class)->orderBy('backed_up_at', 'desc');
    }

    // Scopes
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
        return $query->where('category_id', $categoryId);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%");
        });
    }

    // Methods
    public function createBackup($reason = 'manual')
    {
        $backupData = [
            'title' => $this->title,
            'description' => $this->description,
            'slides' => $this->slides,
            'current_slide_index' => $this->current_slide_index,
            'use_phases' => $this->use_phases,
            'has_initialized_phases' => $this->has_initialized_phases,
            'metadata' => $this->metadata,
            'category_id' => $this->category_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];

        return $this->backups()->create([
            'backup_data' => $backupData,
            'backup_reason' => $reason,
            'backup_type' => $reason === 'auto' ? 'auto' : 'manual',
            'size_bytes' => strlen(json_encode($backupData)),
            'expires_at' => Carbon::now()->addMonths(6) // Keep backups for 6 months
        ]);
    }

    public function restoreFromBackup($backupId)
    {
        $backup = $this->backups()->findOrFail($backupId);
        $backupData = $backup->backup_data;

        $this->update([
            'title' => $backupData['title'],
            'description' => $backupData['description'],
            'slides' => $backupData['slides'],
            'current_slide_index' => $backupData['current_slide_index'],
            'use_phases' => $backupData['use_phases'],
            'has_initialized_phases' => $backupData['has_initialized_phases'],
            'metadata' => $backupData['metadata'],
            'category_id' => $backupData['category_id']
        ]);

        return $this;
    }

    public function getSlideCount()
    {
        $slides = $this->loadSlidesFromFile();
        return count($slides ?? []);
    }

    public function getSize()
    {
        return $this->file_size_bytes ?? 0;
    }

    /**
     * Load slides from file storage
     */
    public function loadSlidesFromFile()
    {
        if (!$this->slides_file_path) {
            return [];
        }

        $fileService = app(\App\Services\PresentationFileService::class);
        return $fileService->loadSlides($this->slides_file_path) ?? [];
    }

    /**
     * Save slides to file storage
     */
    public function saveSlidesToFile(array $slides)
    {
        $fileService = app(\App\Services\PresentationFileService::class);
        $result = $fileService->saveSlides($this->user_id, $this->id, $slides);
        
        $this->update([
            'slides_file_path' => $result['path'],
            'file_size_bytes' => $result['size']
        ]);

        return $result;
    }

    /**
     * Delete slides file
     */
    public function deleteSlidesFile()
    {
        if (!$this->slides_file_path) {
            return true;
        }

        $fileService = app(\App\Services\PresentationFileService::class);
        return $fileService->deleteSlides($this->slides_file_path);
    }

    public function getSizeFormatted()
    {
        $bytes = $this->getSize();
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function getLastBackup()
    {
        return $this->backups()->latest()->first();
    }

    public function isTemplate()
    {
        return $this->is_template;
    }

    public function isPublic()
    {
        return $this->is_public;
    }

    public function isPublished()
    {
        return $this->status === 'published';
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function canBeAccessedBy($user)
    {
        if ($this->user_id === $user->id) {
            return true;
        }

        if ($this->isPublic() && $this->isPublished()) {
            return true;
        }

        if ($user->school_id && $this->school_id === $user->school_id) {
            return true;
        }

        return false;
    }

    // Accessors
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('M j, Y g:i A');
    }

    public function getFormattedUpdatedAtAttribute()
    {
        return $this->updated_at->format('M j, Y g:i A');
    }

    public function getUrlAttribute()
    {
        return route('presentations.show', $this->slug);
    }

    public function getEditUrlAttribute()
    {
        return route('presentations.edit', $this->slug);
    }

    // JSON serialization
    public function toArray()
    {
        $array = parent::toArray();
        $array['slide_count'] = $this->getSlideCount();
        $array['size_formatted'] = $this->getSizeFormatted();
        $array['last_backup'] = $this->getLastBackup();
        $array['category'] = $this->category;
        $array['user'] = $this->user;
        
        return $array;
    }
}
