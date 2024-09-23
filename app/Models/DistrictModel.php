<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class DistrictModel extends Model
{
    use HasFactory;

    protected $table = 'cvmv_location';

    protected $fillable = [
        'location_id',
        'district',
        'district_tam',
        'native_place',
        'native_place_tam',
        'status',
        'create_date',
       
    ];

    public $timestamps = false;

    public static function generateDistId()
    {
        $prefix = 'TN';
    
        // Retry mechanism to handle potential race conditions
        $maxRetries = 5;
        $attempt = 0;
    
        do {
            $attempt++;
            // Fetch the latest record
            $latestMember = self::orderBy('id', 'desc')->first();
    
            // Generate the new number based on the latest ID
            if (!$latestMember) {
                $newNumber = 1;
            } else {
                $latestId = (int) substr($latestMember->location_id, strlen($prefix)); // Adjusted substring to correctly extract the number part
                $newNumber = $latestId + 1;
            }
    
            $newId = $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    
            // Check if the generated ID is unique
            $existingMember = self::where('location_id', $newId)->first();
            
            // If no existing member with the new ID, we can break the loop
            if (!$existingMember) {
                break;
            }
    
            // If we reached the max retries, throw an exception
            if ($attempt >= $maxRetries) {
                throw new \Exception('Failed to generate a unique ID after ' . $maxRetries . ' attempts');
            }
    
            // Wait a short time before retrying
            usleep(100000); // 100ms
    
        } while ($existingMember);
    
        return $newId;
    }
    
}
