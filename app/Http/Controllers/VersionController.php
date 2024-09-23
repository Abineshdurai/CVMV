<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\VersionModel;


class VersionController extends Controller
{
    public function Tsit_Cvmv_Add_Version(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'version_name' => 'required',
            'version_code' => 'required'
        ]);

        if($validator->fails()){
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $current_date = now();

        $version = new VersionModel();
        $version->version_name = $request->input('version_name');
        $version->version_code = $request->input('version_code');
        $version->created_at = $current_date;
        $version->save();

        $success['message'] = "Version added successfully";
        $success['success'] = true;
        return response()->json($success);
    }


    public function Tsit_Cvmv_Check_Version(Request $request, $version_name, $version_code)
    {
        // Query the database to find a matching version
        $version = DB::table('version')
            ->where('version_name', $version_name)
            ->where('version_code', $version_code)
            ->first();
    
        // Check if a matching version was found
        if ($version) {
            // If a match is found, return false message
            return response()->json(['message' => false]);
        } else {
            // If no match is found, return true message
            return response()->json(['message' => true]);
        }
    }
    




}