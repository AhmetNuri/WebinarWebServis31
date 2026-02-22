<?php

namespace Tests\Feature\Api;

use App\Models\DeviceBlacklist;
use App\Models\License;
use App\Models\LicLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseCheckTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['email' => 'test@example.com']);
    }

    public function test_returns_invalid_for_unknown_user(): void
    {
        $response = $this->postJson('/api/v1/license/check', [
            'email'         => 'nobody@example.com',
            'serial_number' => 'NOTEXIST',
        ]);

        $response->assertStatus(200)
            ->assertJson(['valid' => false]);
    }

    public function test_returns_validation_error_when_email_missing(): void
    {
        $response = $this->postJson('/api/v1/license/check', [
            'serial_number' => 'NOTEXIST',
        ]);

        $response->assertStatus(422);
    }

    public function test_returns_valid_for_active_license(): void
    {
        License::factory()->create([
            'user_id'        => $this->user->id,
            'serial_number'  => 'VALIDKEY-001',
            'starts_at'      => now()->subDay(),
            'expires_at'     => now()->addYear(),
            'license_type'   => 'yearly',
            'product_package' => 'Basic',
            'user_enable'    => true,
        ]);

        $response = $this->postJson('/api/v1/license/check', [
            'email'         => $this->user->email,
            'serial_number' => 'VALIDKEY-001',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'valid'   => true,
                'package' => 'Basic',
                'type'    => 'yearly',
            ]);
    }

    public function test_returns_invalid_for_expired_license(): void
    {
        License::factory()->create([
            'user_id'       => $this->user->id,
            'serial_number' => 'EXPIREDKEY',
            'starts_at'     => now()->subYear(2),
            'expires_at'    => now()->subDay(),
            'license_type'  => 'yearly',
            'user_enable'   => true,
        ]);

        $response = $this->postJson('/api/v1/license/check', [
            'email'         => $this->user->email,
            'serial_number' => 'EXPIREDKEY',
        ]);

        $response->assertStatus(200)
            ->assertJson(['valid' => false]);
    }

    public function test_returns_invalid_for_suspended_license(): void
    {
        License::factory()->create([
            'user_id'       => $this->user->id,
            'serial_number' => 'SUSPENDED-KEY',
            'starts_at'     => now()->subDay(),
            'expires_at'    => now()->addYear(),
            'license_type'  => 'yearly',
            'user_enable'   => false,
        ]);

        $response = $this->postJson('/api/v1/license/check', [
            'email'         => $this->user->email,
            'serial_number' => 'SUSPENDED-KEY',
        ]);

        $response->assertStatus(200)
            ->assertJson(['valid' => false]);
    }

    public function test_device_is_bound_on_first_check(): void
    {
        $license = License::factory()->create([
            'user_id'       => $this->user->id,
            'serial_number' => 'BIND-DEV-KEY',
            'starts_at'     => now()->subDay(),
            'expires_at'    => now()->addYear(),
            'license_type'  => 'yearly',
            'user_enable'   => true,
            'device_id'     => null,
        ]);

        $response = $this->postJson('/api/v1/license/check', [
            'email'         => $this->user->email,
            'serial_number' => 'BIND-DEV-KEY',
            'device_id'     => 'DEVICE-ABC',
        ]);

        $response->assertStatus(200)->assertJson(['valid' => true]);
        $this->assertDatabaseHas('licenses', ['id' => $license->id, 'device_id' => 'DEVICE-ABC']);
    }

    public function test_device_mismatch_returns_invalid(): void
    {
        License::factory()->create([
            'user_id'       => $this->user->id,
            'serial_number' => 'MISMATCH-KEY',
            'starts_at'     => now()->subDay(),
            'expires_at'    => now()->addYear(),
            'license_type'  => 'yearly',
            'user_enable'   => true,
            'device_id'     => 'DEVICE-ORIGINAL',
        ]);

        $response = $this->postJson('/api/v1/license/check', [
            'email'         => $this->user->email,
            'serial_number' => 'MISMATCH-KEY',
            'device_id'     => 'DEVICE-OTHER',
        ]);

        $response->assertStatus(200)->assertJson(['valid' => false]);
    }

    public function test_blacklisted_device_returns_invalid(): void
    {
        License::factory()->create([
            'user_id'       => $this->user->id,
            'serial_number' => 'BLACK-KEY',
            'starts_at'     => now()->subDay(),
            'expires_at'    => now()->addYear(),
            'license_type'  => 'yearly',
            'user_enable'   => true,
            'device_id'     => null,
        ]);

        DeviceBlacklist::create(['device_id' => 'BANNED-DEVICE', 'reason' => 'test']);

        $response = $this->postJson('/api/v1/license/check', [
            'email'         => $this->user->email,
            'serial_number' => 'BLACK-KEY',
            'device_id'     => 'BANNED-DEVICE',
        ]);

        $response->assertStatus(200)->assertJson(['valid' => false]);
    }

    public function test_lifetime_license_is_always_valid(): void
    {
        License::factory()->create([
            'user_id'        => $this->user->id,
            'serial_number'  => 'LIFETIME-KEY',
            'starts_at'      => now()->subYear(),
            'expires_at'     => null,
            'license_type'   => 'lifetime',
            'user_enable'    => true,
        ]);

        $response = $this->postJson('/api/v1/license/check', [
            'email'         => $this->user->email,
            'serial_number' => 'LIFETIME-KEY',
        ]);

        $response->assertStatus(200)
            ->assertJson(['valid' => true, 'type' => 'lifetime']);
    }

    public function test_logs_error_for_not_found_license(): void
    {
        $this->postJson('/api/v1/license/check', [
            'email'         => 'nobody@example.com',
            'serial_number' => 'NOTEXIST',
        ]);

        $this->assertDatabaseHas('liclogs', ['level' => 'error', 'event' => 'license.not_found']);
    }

    public function test_warning_returned_when_expiry_near(): void
    {
        License::factory()->create([
            'user_id'       => $this->user->id,
            'serial_number' => 'NEAR-EXP-KEY',
            'starts_at'     => now()->subYear(),
            'expires_at'    => now()->addDays(5),
            'license_type'  => 'yearly',
            'user_enable'   => true,
        ]);

        $response = $this->postJson('/api/v1/license/check', [
            'email'         => $this->user->email,
            'serial_number' => 'NEAR-EXP-KEY',
        ]);

        $response->assertStatus(200)
            ->assertJson(['valid' => true])
            ->assertJsonPath('warning', 'Lisansınızın bitmesine 10 günden az kaldı!');
    }
}
