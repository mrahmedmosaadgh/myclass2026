<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DpTask extends Model
{
    protected $fillable = ['user_id', 'title', 'start_time', 'end_time', 'description', 'type', 'is_active'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
