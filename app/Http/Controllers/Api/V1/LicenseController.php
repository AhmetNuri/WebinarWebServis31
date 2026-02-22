<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeviceBlacklist;
use App\Models\License;
use App\Models\LicLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LicenseController extends Controller
{
    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'         => 'required|email',
            'serial_number' => 'required|string|max:64',
            'device_id'     => 'nullable|string|max:128',
        ]);

        $email        = $validated['email'];
        $serialNumber = $validated['serial_number'];
        $deviceId     = $validated['device_id'] ?? null;
        $ip           = $request->ip();

        // Find user
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->logEvent(null, null, 'error', 'license.not_found', 'Kullanıcı bulunamadı: ' . $email, $deviceId, $ip);

            return response()->json([
                'valid'   => false,
                'message' => 'Lisans bulunamadı.',
            ], 200);
        }

        // Check if user is suspended
        if (! $user->is_admin && $request->has('user_enable')) {
            // handled per license below
        }

        // Build query
        $query = License::where('user_id', $user->id)
            ->where('serial_number', $serialNumber);

        if ($deviceId !== null) {
            // device_id geldi: email + serial + device_id ile sorgula
            $query->where(function ($q) use ($deviceId) {
                $q->where('device_id', $deviceId)
                  ->orWhereNull('device_id');
            });
        }

        $license = $query->first();

        if (! $license) {
            $this->logEvent(null, $user->id, 'error', 'license.not_found', 'Lisans bulunamadı. SN: ' . $serialNumber, $deviceId, $ip);

            return response()->json([
                'valid'   => false,
                'message' => 'Lisans bulunamadı.',
            ], 200);
        }

        // Check user_enable flag
        if (! $license->user_enable) {
            $this->logEvent($license->id, $user->id, 'error', 'license.suspended', 'Lisans askıya alındı. SN: ' . $serialNumber, $deviceId, $ip);

            return response()->json([
                'valid'   => false,
                'message' => 'Lisansınız askıya alınmıştır. Lütfen destek ile iletişime geçin.',
            ], 200);
        }

        // Check device blacklist
        if ($deviceId !== null) {
            $blacklisted = DeviceBlacklist::where('device_id', $deviceId)->exists();
            if ($blacklisted) {
                $this->logEvent($license->id, $user->id, 'error', 'license.blacklisted_device', 'Kara listedeki cihaz: ' . $deviceId, $deviceId, $ip);

                return response()->json([
                    'valid'   => false,
                    'message' => 'Bu cihaz kara listeye alınmıştır.',
                ], 200);
            }
        }

        // Bind device_id if not set
        if ($deviceId !== null && $license->device_id === null) {
            $license->device_id = $deviceId;
            $this->logEvent($license->id, $user->id, 'info', 'license.device_bound', 'Cihaz bağlandı: ' . $deviceId, $deviceId, $ip);
        } elseif ($deviceId !== null && $license->device_id !== $deviceId) {
            // Device mismatch
            $this->logEvent($license->id, $user->id, 'error', 'license.device_mismatch', 'Cihaz eşleşmedi. Beklenen: ' . $license->device_id . ' Gelen: ' . $deviceId, $deviceId, $ip);

            return response()->json([
                'valid'   => false,
                'message' => 'Cihaz eşleşmedi. Bu lisans farklı bir cihaza kayıtlıdır.',
            ], 200);
        }

        // Check expiry
        if (! $license->isActive()) {
            $this->logEvent($license->id, $user->id, 'error', 'license.expired', 'Lisans süresi doldu. SN: ' . $serialNumber, $deviceId, $ip);

            return response()->json([
                'valid'   => false,
                'message' => 'Lisans süresi dolmuştur.',
            ], 200);
        }

        // Update last checked info
        $license->last_checked_date      = now();
        $license->last_checked_device_id = $deviceId;
        $license->save();

        $daysLeft = $license->daysLeft();
        $warning  = null;

        if ($daysLeft !== null && $daysLeft < 10) {
            $warning = 'Lisansınızın bitmesine 10 günden az kaldı!';
        }

        $response = [
            'valid'     => true,
            'expires_at' => $license->expires_at?->format('Y-m-d'),
            'days_left' => $daysLeft,
            'package'   => $license->product_package,
            'type'      => $license->license_type,
            'emergency' => $license->emergency,
        ];

        if ($warning !== null) {
            $response['warning'] = $warning;
        }

        return response()->json($response, 200);
    }

    private function logEvent(
        ?int $licenseId,
        ?int $userId,
        string $level,
        string $event,
        string $message,
        ?string $deviceId,
        ?string $ip
    ): void {
        LicLog::create([
            'license_id' => $licenseId,
            'user_id'    => $userId,
            'level'      => $level,
            'event'      => $event,
            'message'    => $message,
            'device_id'  => $deviceId,
            'ip_address' => $ip,
        ]);
    }
}
