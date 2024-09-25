<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerificationModel extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'cvmv_email_verification';

    protected $fillable = [
        'email', 'otp', 'created_at',
    ];
}
