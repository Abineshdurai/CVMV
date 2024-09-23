<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChildrenModel extends Model 
{
    use HasFactory;
    public $timestamp = false;
    protected $table = 'cvmv_children';
    protected $fillable = [
        'mem_id',
        'children_id',
        'children_name',
        'relation',
        'children_dob',
        'children_education',
        'children_professional',
      //  'children_phone',
        'status',
        'created_at',
       
    ];
}