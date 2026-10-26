<?php

namespace App\Services;

use App\Models\LocationSettingsModel;
use App\Models\Setting;
use App\Models\User;

class AttendanceLocationService
{
    public function isEnabledFor(User $user): bool
    {
        return (bool) ($this->branchSettings($user)?->location_check_on_login ?? false);
    }

    public function validateForUser(User $user, ?float $latitude, ?float $longitude): array
    {
        if (! $this->isEnabledFor($user)) {
            return [
                'allowed' => true,
                'enabled' => false,
                'message' => null,
                'office' => null,
                'distance_meters' => null,
            ];
        }

        if ($latitude === null || $longitude === null) {
            return [
                'allowed' => false,
                'enabled' => true,
                'message' => 'GPS location is required to check in.',
                'office' => null,
                'distance_meters' => null,
            ];
        }

        $office = LocationSettingsModel::query()->latest('id')->first();
        if (! $office || ! is_numeric($office->latitude) || ! is_numeric($office->longitude) || ! is_numeric($office->radius)) {
            return [
                'allowed' => false,
                'enabled' => true,
                'message' => 'Office location is not configured.',
                'office' => $office,
                'distance_meters' => null,
            ];
        }

        $distanceMeters = $this->distanceMeters(
            (float) $latitude,
            (float) $longitude,
            (float) $office->latitude,
            (float) $office->longitude
        );

        if ($distanceMeters > (float) $office->radius) {
            return [
                'allowed' => false,
                'enabled' => true,
                'message' => 'You must be within the configured office radius to check in.',
                'office' => $office,
                'distance_meters' => $distanceMeters,
            ];
        }

        return [
            'allowed' => true,
            'enabled' => true,
            'message' => null,
            'office' => $office,
            'distance_meters' => $distanceMeters,
        ];
    }

    public function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000.0;

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2
            + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(max(0.0, 1 - $a)));

        return $earthRadius * $c;
    }

    protected function branchSettings(User $user): ?Setting
    {
        $branchId = $user->branch_id ?: $user->id;

        return Setting::query()
            ->where('branch_id', $branchId)
            ->first()
            ?? Setting::query()->first();
    }
}
