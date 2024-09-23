<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatrimonyImagesModel extends Model{
    use HasFactory;
    public $timestamp = false;
    protected $table = 'cvmv_matri_images';
    protected $fillable = [
        'matri_id',
        'matri_img_id',
        'matri_image',
        'status',
        'created_at',
        'updated_at',
    ];

    public static function generateMatImage()
    {
        $prefix = 'MATIMG';
        $latestMember = self::orderBy('id', 'desc')->first();
        if (!$latestMember) {
            $newNumber = 1;
        } else {
            $latestId = (int) substr($latestMember->matri_image, 6);
            $newNumber = $latestId + 1;
        }
        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}