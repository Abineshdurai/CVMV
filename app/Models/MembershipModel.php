<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipModel extends Model
{
    use HasFactory;
    public $timestamp = false;
    protected $table = 'cvmv_membership';
    protected $fillable = [
        'mem_id',
        'mem_token',
        'first_name',
        'last_name',
        'phone',
        'gender',
        'date_of_birth',
        'father_name',
        'mother_name',
        'qualification',
        'job_designation',
        'marriage_date',
        'blood_group',
        'vagaiyara',
        'kula_deivam',
        'temple_place',
        'district',
        'native_place',
        'address',
        'wife_name',
        'wife_dob',
        'wife_phone',
        'wife_qualification',
        'wife_birth_place',
        'wife_job_designation',
        'wife_district',
        'member_image',
        'payment_status',
        'mem_status',
        'mem_type',
        'status',
        'approval_status',
        'last_login',
        'created_at',
        'updated_at'
      
    ];
    public static function generateMemId()
    {
        $prefix = 'CVMV';
        $latestMember = self::orderBy('id', 'desc')->first();
        if (!$latestMember) {
            $newNumber = 1;
        } else {
            $latestId = (int) substr($latestMember->mem_id, 6);
            $newNumber = $latestId + 1;
        }
        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
