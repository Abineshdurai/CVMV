<?php
namespace App\Http\Controllers;

use App\Models\DistrictModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class DistrictController extends Controller
{
    public function add_dist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'district' => 'required',
            'district_tam' => 'required',
            'native_place' => 'required',
            'native_place_tam' => 'required'
        ]);
    
        if ($validator->fails()) {
            return response($validator->messages(), 200);
        } else {
            $location_id = DistrictModel::generateDistId();
    
            $dist = new DistrictModel([
                'location_id' => $location_id,
                'district' => $request->district,
                'district_tam' => $request->district_tam,
                'native_place' => $request->native_place,
                'native_place_tam' => $request->native_place_tam,
                'status' => 'active',
                'create_date' => now(),
                // 'updated_at' => null, // not needed because it's handled by default
            ]);
            $dist->save();
        }
    
        return response()->json([
            'success' => true,
            'message' => 'District added successfully'
        ]);
    }


   

    public function get_dist(Request $request)
    {
        $dists = DistrictModel::where('status', 'active')
            ->select('location_id', 'district', 'district_tam')
            ->get()
            ->unique('district');
    
        if ($dists->isEmpty()) {
            return response()->json([
                'message' => 'Location not found',
                'success' => false
            ], 404);
        } else {
            $result = [];
    
            foreach ($dists as $dist) {
                $result[] = [
                    'location_id' => $dist->location_id,
                    'district' => $dist->district,
                    'district_tam' => $dist->district_tam
                ];
            }
    
            return response()->json([
                'message' => 'Records fetched successfully',
                'success' => true,
                'district' => $result
            ], 200);
        }
    
        return response()->json([
            'message' => 'Records fetched successfully',
            'success' => false,
        ], 500);
    }
    
    
    public function get_native(Request $request, $district)
{
    // Check if the district is active in the cvmv_location table
    $dist = DB::table('cvmv_location')
        ->where(function ($query) use ($district) {
            $query->where('district', $district)
                  ->orWhere('district_tam', $district);
        })
        ->where('status', 'active')
        ->first();

    if (!$dist) {
        return response()->json([
            'message' => 'District is not active',
            'success' => false
        ], 400);
    }

    // Fetch native places for the specified district
    $natives = DistrictModel::where('status', 'active')
        ->where(function ($query) use ($district) {
            $query->where('district', $district)
                  ->orWhere('district_tam', $district);
        })
        ->select('location_id', 'native_place', 'native_place_tam')
        ->get();

    if ($natives->isEmpty()) {
        return response()->json([
            'message' => 'No native places found for this district',
            'success' => false
        ], 404);
    }

    // Prepare response with native places
    $result = [];
    foreach ($natives as $native) {
        $result[] = [
            'location_id' => $native->location_id,
            'native_place' => $native->native_place,
            'native_place_tam' => $native->native_place_tam
        ];
    }

    return response()->json([
        'message' => 'Records fetched successfully',
        'success' => true,
        'native' => $result
    ], 200);
}

    
    
}