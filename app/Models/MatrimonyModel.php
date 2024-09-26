<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatrimonyModel extends Model
{
    use HasFactory;
    public $timestamp = false;
    protected $table = 'cvmv_matrimony';
    protected $fillable = [
            'matri_user_id',
            'matri_id',
            'name',
            'phone',
            'm_status',
            'email',
            'date_of_birth',
            'blood_group',
            'qualification',
            'kula_deivam',
            'temple_place',
            'm_height',
            'm_weight',
            'm_color',
            'district',
            'native_place',
            'address',
            'gender',
            'job_designation',
            'job_location',
            'job_annual_income',
            'father_name',
            'father_occupation',
            'father_number',
            'mother_name',
            'mother_occupation',
            'mother_number',
            'contact_detail',
            'j_rasi',
            'j_nakshatra',
            'j_dhosam',
            'horoscope_attach',
            'no_of_brothers',
            'no_of_sisters',
            'm_count',
            'matri_images',
            'mat_status',
            'last_login',
            'created_at',
            'updated_at'

    ];
}

