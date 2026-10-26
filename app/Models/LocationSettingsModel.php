<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationSettingsModel extends Model
{
    protected $table = 'location_settings';

    protected $fillable = [
        'latitude',
        'longitude',
        'radius',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;

    /**
     * Get the current location settings
     */
    public function getSettings()
    {
        return $this->first();
    }
}
