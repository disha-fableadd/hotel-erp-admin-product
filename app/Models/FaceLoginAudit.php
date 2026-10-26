<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaceLoginAudit extends Model
{
    protected $table = 'face_login_audits';

    protected $fillable = [
        'user_id',
        'branch_id',
        'status',
        'method',
        'distance',
        'confidence',
        'ip_address',
        'user_agent',
        'message',
    ];
}
