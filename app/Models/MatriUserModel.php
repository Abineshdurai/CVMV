<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatriUserModel extends Model 
{
    use HasFactory;
    public $timestamp = false;
    protected $table = 'cvmv_matri_user';
    protected $fillable = [
        'matri_user_id',
        'matri_token',
        'matri_user_name',
        'matri_user_proof',
        'matri_user_address',
        'matri_user_phone',
       // 'children_phone',
        'status',
        'created_at',
        'updated_at'
       
    ];
}