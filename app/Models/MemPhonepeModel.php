<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class MemPhonepeModel extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'membership_phonepe';
    protected $fillable = [
       'response',
       'status',
        'created_at'
    ];
}
