<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class PresentationBackup extends Model
{
    use HasFactory;

    protected $fillable = [
        'presentation_id',
        'backup_data',
        'backup_reason',
        'backup_type',
        'size_bytes',
        'backed_up_at',
        'expires_at'
    ];

    protected $casts = [
        'backup_data' => 'array',
        'size_bytes' => 'integer'
    ];

    protected $dates = [
        'backed_up_at',
        'expires_at'
    ];

    public $timestamps = false;

    // Relationships
    public function presentation()
    {
        return $this->belongsTo(Presentation::class);
    }

    // Scopes
    public function scopeManual($query)
    {
        return $query->where('backup_type', 'manual');
    }

    public function scopeAuto($query)
    {
        return $query->where('backup_type', 'auto');
    }

    public function scopeScheduled($query)
    {
        return $query->where('backup_type', 'scheduled');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', Carbon::now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', Carbon::now());
        });
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('backed_up_at', '>=', Carbon::now()->subDays($days));
    }

    // Methods
    public function getSizeFormatted()
    {
        $bytes = $this->size_bytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getDaysUntilExpiry()
    {
        if (!$this->expires_at) {
            return null;
        }

        return $this->expires_at->diffInDays(Carbon::now(), false);
    }

    public function restore()
    {
        return $this->presentation->restoreFromBackup($this->id);
    }

    // Accessors
    public function getFormattedBackedUpAtAttribute()
    {
        return $this->backed_up_at->format('M j, Y g:i A');
    }

    public function getFormattedExpiresAtAttribute()
    {
        return $this->expires_at ? $this->expires_at->format('M j, Y g:i A') : null;
    }

    public function getSizeFormattedAttribute()
    {
        return $this->getSizeFormatted();
    }

    // JSON serialization
    public function toArray()
    {
        $array = parent::toArray();
        $array['size_formatted'] = $this->getSizeFormatted();
        $array['is_expired'] = $this->isExpired();
        $array['days_until_expiry'] = $this->getDaysUntilExpiry();
        
        return $array;
    }
}
