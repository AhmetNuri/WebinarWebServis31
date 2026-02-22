<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\License>
 */
class LicenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'             => User::factory(),
            'device_id'           => null,
            'serial_number'       => strtoupper(Str::random(8) . '-' . Str::random(8) . '-' . Str::random(8)),
            'starts_at'           => now()->subDays(rand(1, 30)),
            'last_checked_date'   => null,
            'last_checked_device_id' => null,
            'emergency'           => false,
            'expires_at'          => now()->addYear(),
            'license_type'        => 'yearly',
            'product_package'     => 'Basic',
            'user_enable'         => true,
            'max_connection_count' => 1,
        ];
    }
}
