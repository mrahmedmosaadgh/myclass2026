<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Documentation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type',
        'status',
        'tags',
        'author_id'
    ];

    protected $casts = [
        'content' => 'array',
        'tags' => 'array'
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public static function getTypes()
    {
        return [
            ['value' => 'note', 'label' => 'Note'],
            ['value' => 'guide', 'label' => 'Guide'],
            ['value' => 'tutorial', 'label' => 'Tutorial'],
            ['value' => 'reference', 'label' => 'Reference'],
        ];
    }

    public static function getStatuses()
    {
        return [
            ['value' => 'draft', 'label' => 'Draft'],
            ['value' => 'published', 'label' => 'Published'],
            ['value' => 'archived', 'label' => 'Archived'],
        ];
    }
}





