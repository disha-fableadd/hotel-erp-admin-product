<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacebookAppConfiguration extends Model
{
    use HasFactory;

    protected $table = 'facebook_app_configurations';

    protected $fillable = [
        'branch_id',
        'facebook_app_id',
        'facebook_app_secret',
        'phone_number_id',
        'whatsapp_business_account_id',
        'access_token',
        'webhook_url',
        'isDeleted',
    ];

    public function branch()
    {
        return $this->belongsTo(User::class, 'branch_id');
    }

    public function templates()
    {
        return $this->hasMany(WhatsAppMessageTemplate::class, 'facebook_app_configuration_id');
    }
}
