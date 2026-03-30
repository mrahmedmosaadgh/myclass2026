<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class CrPresentationCategory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cr_presentation_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'parent_id',
        'sort_order',
        'school_id',
        'created_by',
        'is_system',
        'is_active'
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    protected $dates = [
        'deleted_at'
    ];

    // Boot method for automatic slug generation
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug = static::generateUniqueSlug($category->name);
        });

        static::updating(function ($category) {
            if ($category->isDirty('name')) {
                $category->slug = static::generateUniqueSlug($category->name);
            }
        });
    }

    /**
     * Generate unique slug for category
     */
    protected static function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    // Relationships
    public function parent()
    {
        return $this->belongsTo(CrPresentationCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(CrPresentationCategory::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function presentations()
    {
        return $this->hasMany(Presentation::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Methods
    public function getPresentationCount()
    {
        return $this->presentations()->count();
    }

    public function getPresentationCountForUser($userId)
    {
        return $this->presentations()->where('user_id', $userId)->count();
    }

    public function getFullPath()
    {
        $path = collect([$this->name]);
        $parent = $this->parent;

        while ($parent) {
            $path->prepend($parent->name);
            $parent = $parent->parent;
        }

        return $path->implode(' > ');
    }

    public function getDescendants()
    {
        $descendants = collect();
        $children = $this->children;

        while ($children->isNotEmpty()) {
            $descendants = $descendants->merge($children);
            $children = $children->flatMap(function ($child) {
                return $child->children;
            });
        }

        return $descendants;
    }

    public function getAllPresentationIds()
    {
        $ids = $this->presentations()->pluck('id');
        
        foreach ($this->getDescendants() as $descendant) {
            $ids = $ids->merge($descendant->presentations()->pluck('id'));
        }

        return $ids->unique();
    }

    public function canBeDeletedBy($user)
    {
        // System categories cannot be deleted
        if ($this->is_system) {
            return false;
        }

        // Only creator or school admin can delete
        if ($this->created_by === $user->id) {
            return true;
        }

        if ($user->hasRole('school_admin') && $this->school_id === $user->school_id) {
            return true;
        }

        return false;
    }

    public function canBeEditedBy($user)
    {
        // System categories cannot be edited
        if ($this->is_system) {
            return false;
        }

        // Only creator or school admin can edit
        if ($this->created_by === $user->id) {
            return true;
        }

        if ($user->hasRole('school_admin') && $this->school_id === $user->school_id) {
            return true;
        }

        return false;
    }

    // Accessors
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('M j, Y g:i A');
    }

    public function getPresentationCountAttribute()
    {
        return $this->getPresentationCount();
    }

    // Tree structure helpers
    public static function getTree($schoolId = null)
    {
        $query = static::active()->ordered();
        
        if ($schoolId) {
            $query->forSchool($schoolId);
        }

        $categories = $query->get();
        $rootCategories = $categories->whereNull('parent_id');

        return $rootCategories->map(function ($category) use ($categories) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'color' => $category->color,
                'icon' => $category->icon,
                'presentation_count' => $category->getPresentationCount(),
                'children' => static::buildChildrenTree($category, $categories)
            ];
        });
    }

    protected static function buildChildrenTree($category, $allCategories)
    {
        $children = $allCategories->where('parent_id', $category->id);
        
        return $children->map(function ($child) use ($allCategories) {
            return [
                'id' => $child->id,
                'name' => $child->name,
                'slug' => $child->slug,
                'description' => $child->description,
                'color' => $child->color,
                'icon' => $child->icon,
                'presentation_count' => $child->getPresentationCount(),
                'children' => static::buildChildrenTree($child, $allCategories)
            ];
        })->toArray();
    }

    // JSON serialization
    public function toArray()
    {
        $array = parent::toArray();
        $array['presentation_count'] = $this->getPresentationCount();
        $array['full_path'] = $this->getFullPath();
        $array['children_count'] = $this->children()->count();
        
        return $array;
    }
}
