<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicLog extends Model
{
    protected $table = 'liclogs';

    protected $fillable = [
        'license_id',
        'user_id',
        'level',
        'event',
        'message',
        'device_id',
        'ip_address',
    ];

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
