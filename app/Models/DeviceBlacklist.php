<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceBlacklist extends Model
{
    protected $fillable = [
        'device_id',
        'reason',
    ];
}
