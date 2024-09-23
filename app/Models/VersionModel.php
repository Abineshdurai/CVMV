<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VersionModel extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = 'version';
    protected $fillable = [
        'version_name',
        'version_code',
        'created_at',
        'updated_at'
    
    ];
}