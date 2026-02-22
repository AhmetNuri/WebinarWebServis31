<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class License extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'device_id',
        'serial_number',
        'starts_at',
        'last_checked_date',
        'last_checked_device_id',
        'emergency',
        'expires_at',
        'license_type',
        'product_package',
        'user_enable',
        'max_connection_count',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'            => 'datetime',
            'last_checked_date'    => 'datetime',
            'expires_at'           => 'datetime',
            'emergency'            => 'boolean',
            'user_enable'          => 'boolean',
            'max_connection_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function liclogs(): HasMany
    {
        return $this->hasMany(LicLog::class);
    }

    public function isActive(): bool
    {
        if (! $this->user_enable) {
            return false;
        }

        if ($this->license_type === 'lifetime') {
            return true;
        }

        if ($this->expires_at === null) {
            return true;
        }

        return $this->expires_at->isFuture();
    }

    public function daysLeft(): ?int
    {
        if ($this->license_type === 'lifetime') {
            return null;
        }

        if ($this->expires_at === null) {
            return null;
        }

        $diff = now()->diffInDays($this->expires_at, false);

        return (int) max(0, $diff);
    }
}
