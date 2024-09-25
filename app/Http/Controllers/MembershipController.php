<?php

namespace App\Http\Controllers;

use App\Models\ChildrenModel;
use App\Models\DistrictModel;
use Illuminate\Support\Facades\Log;

use App\Models\MemPhonepeResponseModel;
use App\Models\MembershipModel;
use App\Models\MemPhonepeModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MembershipController extends Controller
{
    public function sendmobileOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|digits:10'
        ]);


        if ($validator->fails()) {

            return response($validator->messages(), 200);
        } else {
            $success['message'] = "Mobile OTP sent successfully";
            $success['success'] = true;
            return response()->json($success);
        }
    }

    // public function verifyOTP(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'otp' => 'required|numeric',
    //         'phone' => 'required|numeric|digits:10',
    //     ]);

    //     if ($validator->fails()) {

    //         return response($validator->messages(), 200);
    //     } else {
    //         $otp = $request->input('otp');
    //         $mobile = $request->input('phone');

    //         $isregister = DB::table('cvmv_membership')
    //             ->select('phone')
    //             ->where('phone', '=', $mobile)
    //             ->value('phone');

    //         if (!$isregister) {
    //             $success['message'] = "Not registered";
    //             $success['success'] = true;
    //             return response()->json($success);
    //         } else {
    //             $mem_token = tokenKey($mobile);
    //             $update1 = DB::table('cvmv_membership')
    //                 ->where('phone', '=', $mobile)
    //                 ->update(["mem_token" => '1']);
    //             $update = DB::table('cvmv_membership')
    //                 ->where('phone', '=', $mobile)
    //                 ->update(["mem_token" => $mem_token]);

    //             if (!$update) {
    //                 $fail['message'] = "Authentication failed";
    //                 $fail['success'] = false;
    //                 return response()->json($fail);
    //             } else {
    //                 $success['success'] = true;
    //                 $success['message'] = "login success";
    //                 $success['mem_token'] = $mem_token;
    //                 return response()->json($success);
    //             }
    //         }
    //     }
    // }


    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|numeric',
            'phone' => 'required|numeric|digits:10',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        } else {
            $otp = $request->input('otp');
            $mobile = $request->input('phone');

            // Check if the phone is registered in the cvmv_membership table
            $isRegisteredInMembership = DB::table('cvmv_membership')
                ->where('phone', '=', $mobile)
                ->exists();

            // Retrieve the matri_user_id from the cvmv_matri_user table
            $matri_user_id = DB::table('cvmv_matri_user')
                ->where('matri_user_phone', '=', $mobile)
                ->value('matri_user_id');

            // Initialize tokens
            $mem_token = null;
            $matri_token = null;

            // Process cvmv_membership table
            if ($isRegisteredInMembership) {
                $mem_token = tokenKey($mobile);
                $updateMemToken1 = DB::table('cvmv_membership')
                    ->where('phone', '=', $mobile)
                    ->update(["mem_token" => "1"]);
                $updateMemToken = DB::table('cvmv_membership')
                    ->where('phone', '=', $mobile)
                    ->update(["mem_token" => $mem_token]);

                if (!$updateMemToken) {
                    $fail['message'] = "Failed to update membership token";
                    $fail['success'] = false;
                    return response()->json($fail);
                }
            }

            // Process cvmv_matri_user table
            if ($matri_user_id) {
                $matri_token = tokenKey($matri_user_id);
                $updateMatriToken1 = DB::table('cvmv_matri_user')
                    ->where('matri_user_phone', '=', $mobile)
                    ->update(["matri_token" => "1"]);
                $updateMatriToken = DB::table('cvmv_matri_user')
                    ->where('matri_user_phone', '=', $mobile)
                    ->update(["matri_token" => $matri_token]);

                if (!$updateMatriToken) {
                    $fail['message'] = "Failed to update matrimony token";
                    $fail['success'] = false;
                    return response()->json($fail);
                }
            }

            // If both updates are successful, return success response
            $success['success'] = true;
            $success['message'] = "Login success";
            if ($mem_token) {
                $success['mem_token'] = $mem_token;
            }
            if ($matri_token) {
                $success['matri_token'] = $matri_token;
            }

            return response()->json($success);
        }
    }


    // public function validateToken(Request $request)
    // {
    //     $token = $request->bearerToken();

    //     if (!$token) {
    //         return response()->json(['success' => false, 'message' => 'Token not provided'], 401);
    //     }

    //     Log::info('Token provided: ' . $token);

    //     $isMemTokenValid = DB::table('cvmv_membership')
    //         ->where('mem_token', $token)
    //         ->exists();

    //     Log::info('Membership token valid: ' . ($isMemTokenValid ? 'Yes' : 'No'));

    //     $isMatriTokenValid = DB::table('cvmv_matri_user')
    //         ->where('matri_token', $token)
    //         ->exists();

    //     Log::info('Matri token valid: ' . ($isMatriTokenValid ? 'Yes' : 'No'));

    //     if ($isMemTokenValid && $isMatriTokenValid) {
    //         return response()->json(['success' => true, 'message' => 'Token is valid in both tables'], 200);
    //     } else {
    //         return response()->json(['success' => false, 'message' => 'Token is invalid in one or both tables'], 401);
    //     }
    // }

    public function validateToken(Request $request)
{
    // Get both tokens from the request
    $memToken = $request->input('mem_token');
    $matriToken = $request->input('matri_token');

    // Check if both tokens are provided
    if (!$memToken || !$matriToken) {
        return response()->json(['success' => false, 'message' => 'Both tokens must be provided'], 400);
    }

    // Validate mem_token
    $isMemTokenValid = DB::table('cvmv_membership')
        ->where('mem_token', $memToken)
        ->exists();

    // Validate matri_token
    $isMatriTokenValid = DB::table('cvmv_matri_user')
        ->where('matri_token', $matriToken)
        ->exists();

    // Check if both tokens are valid
    if ($isMemTokenValid && $isMatriTokenValid) {
        return response()->json(['success' => true, 'message' => 'Both tokens are valid'], 200);
    }
    if ($isMemTokenValid) {
        return response()->json(['success' => true, 'message' => 'Mem Token token is valid'], 200);
    }
    if ($isMatriTokenValid) {
        return response()->json(['success' => true, 'message' => 'Matri Token token is valid'], 200);
    }
     else {
        return response()->json(['success' => true, 'message' => 'One or both tokens are invalid'], 401);
    }
}





    public function create_personal_membership(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required|numeric|digits:10|unique:cvmv_membership,phone',
            'gender' => 'required',
            'date_of_birth' => 'required',
            'father_name' => 'required',
            'mother_name' => 'required',
            'qualification' => 'required',
            'job_designation' => 'required',
            'marriage_date' => 'required',
            'blood_group' => 'required',
            'vagaiyara' => 'required',
            'kula_deivam' => 'required',
            'temple_place' => 'required',
            'district' => 'required',
            'native_place' => 'required',
            'address' => 'required',
            // 'wife_name' => 'required',
            // 'wife_dob' => 'required',
            // 'wife_phone' => 'required',
            // 'wife_qualification' => 'required',
            // 'wife_birth_place' => 'required',
            // 'wife_job_designation' => 'required',
            // 'wife_district' => 'required',
            'member_image' => 'required',
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 200);
        }

        date_default_timezone_set('Asia/Calcutta');

        $mem_id = MembershipModel::generateMemId();
        $mobile = $request->input('phone');
        $mem_token = tokenKey($mobile);

        if ($request->hasFile('member_image')) {
            $member_image = $request->file('member_image');
            $time = time();
            $img_id = date("dmyHis", $time);
            $path = $mem_id . "." . $member_image->getClientOriginalExtension();
            $directoryPath = public_path('../uploads/images/member_images/');
            \Illuminate\Support\Facades\File::makeDirectory($directoryPath, $mode = 0777, true, true);
            $member_image->move($directoryPath, $path);
        } else {
            $path = '';
        }

        $member = new MembershipModel([
            'mem_id' => $mem_id,
            'mem_token' => $mem_token,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'father_name' => $request->father_name,
            'mother_name' => $request->mother_name,
            'qualification' => $request->qualification,
            'job_designation' => $request->job_designation,
            'marriage_date' => $request->marriage_date,
            'blood_group' => $request->blood_group,
            'vagaiyara' => $request->vagaiyara,
            'kula_deivam' => $request->kula_deivam,
            'temple_place' => $request->temple_place,
            'district' => $request->district,
            'native_place' => $request->native_place,
            'address' => $request->address,
            'wife_name' => $request->wife_name,
            'wife_dob' => $request->wife_dob,
            'wife_phone' => $request->wife_phone,
            'wife_qualification' => $request->wife_qualification,
            'wife_birth_place' => $request->wife_birth_place,
            'wife_job_designation' => $request->wife_job_designation,
            'wife_district' => $request->wife_district,
            'member_image' => $path,
            'status' => 'active',
            'approval_status' => 'pending',
            'last_login' => now(),
            'created_at' => now(),
            // 'updated_at' => '0000-00-00 00:00:00'

        ]);
        $member->save();

        if ($request->has('children_name')) {
            for ($i = 0; $i < count($request->children_name); $i++) {
                $children_id = $this->generateChildrenID($request->input('first_name'));

                $children = new ChildrenModel([
                    'mem_id' => $mem_id,
                    'children_id' => $children_id,
                    'children_name' => $request->children_name[$i],
                    'relation' => $request->relation[$i],
                    'children_dob' => $request->children_dob[$i],
                    'children_education' => $request->children_education[$i],
                    'children_professional' => $request->children_professional[$i],
                    // 'children_phone' => $request->children_phone[$i],
                    'status' => 'active',
                    'created_at' => now(),
                ]);
                $children->save();
            }
        }

        return response()->json([
            'success' => true,
            'mem_token' => $mem_token,
            'message' => 'Member and Children added successfully'
        ]);
    }





    public function temp_create_personal_membership(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mem_id' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required|numeric|digits:10|unique:cvmv_membership,phone',
            'gender' => 'required',
            'date_of_birth' => 'required',
            'father_name' => 'required',
            'mother_name' => 'required',
            'qualification' => 'required',
            'job_designation' => 'required',
            'marriage_date' => 'required',
            'blood_group' => 'required',
            'vagaiyara' => 'required',
            'kula_deivam' => 'required',
            'temple_place' => 'required',
            'district' => 'required',
            'native_place' => 'required',
            'address' => 'required',
            // 'wife_name' => 'required',
            // 'wife_dob' => 'required',
            // 'wife_phone' => 'required',
            // 'wife_qualification' => 'required',
            // 'wife_birth_place' => 'required',
            // 'wife_job_designation' => 'required',
            // 'wife_district' => 'required',
            'member_image' => 'required',
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 200);
        }

        date_default_timezone_set('Asia/Calcutta');

        $mem_id = $request->input('mem_id');
        $mobile = $request->input('phone');
        $mem_token = tokenKey($mobile);

        if ($request->hasFile('member_image')) {
            $member_image = $request->file('member_image');
            $time = time();
            $img_id = date("dmyHis", $time);
            $path = $mem_id . "." . $member_image->getClientOriginalExtension();
            $directoryPath = public_path('../uploads/images/member_images/');
            \Illuminate\Support\Facades\File::makeDirectory($directoryPath, $mode = 0777, true, true);
            $member_image->move($directoryPath, $path);
        } else {
            $path = '';
        }

        $member = new MembershipModel([
            'mem_id' => $mem_id,
            'mem_token' => $mem_token,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'father_name' => $request->father_name,
            'mother_name' => $request->mother_name,
            'qualification' => $request->qualification,
            'job_designation' => $request->job_designation,
            'marriage_date' => $request->marriage_date,
            'blood_group' => $request->blood_group,
            'vagaiyara' => $request->vagaiyara,
            'kula_deivam' => $request->kula_deivam,
            'temple_place' => $request->temple_place,
            'district' => $request->district,
            'native_place' => $request->native_place,
            'address' => $request->address,
            'wife_name' => $request->wife_name,
            'wife_dob' => $request->wife_dob,
            'wife_phone' => $request->wife_phone,
            'wife_qualification' => $request->wife_qualification,
            'wife_birth_place' => $request->wife_birth_place,
            'wife_job_designation' => $request->wife_job_designation,
            'wife_district' => $request->wife_district,
            'member_image' => $path,
            'status' => 'active',
            'approval_status' => 'pending',
            'last_login' => now(),
            'created_at' => now(),
            // 'updated_at' => '0000-00-00 00:00:00'

        ]);
        $member->save();

        if ($request->has('children_name')) {
            for ($i = 0; $i < count($request->children_name); $i++) {
                $children_id = $this->generateChildrenID($request->input('first_name'));

                $children = new ChildrenModel([
                    'mem_id' => $mem_id,
                    'children_id' => $children_id,
                    'children_name' => $request->children_name[$i],
                    'relation' => $request->relation[$i],
                    'children_dob' => $request->children_dob[$i],
                    'children_education' => $request->children_education[$i],
                    'children_professional' => $request->children_professional[$i],
                    // 'children_phone' => $request->children_phone[$i],
                    'status' => 'active',
                    'created_at' => now(),
                ]);
                $children->save();
            }
        }

        return response()->json([
            'success' => true,
            'mem_token' => $mem_token,
            'message' => 'Member and Children added successfully'
        ]);
    }


    private function generateChildrenID(string $first_name): string
    {
        $namePrefix = strtoupper(substr($first_name, 0, 3));
        $isUnique = false;
        $childrenID = '';

        while (!$isUnique) {
            $childrenID = $namePrefix . rand(10000, 99999);
            $existingID = DB::table('cvmv_children')->where('children_id', $childrenID)->first();

            if (!$existingID) {
                $isUnique = true;
            } else {
                $childrenID = $namePrefix . rand(10000, 99999);
                $existingID = DB::table('cvmv_children')->where('children_id', $childrenID)->first();
                if (!$existingID) {
                    $isUnique = true;
                }
            }
        }

        return $childrenID;
    }


    public function Tsit_Cvmv_Add_Children(Request $request, $mem_id)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'children_name' => 'required|array',
            'relation' => 'required|array',
            'children_dob' => 'required|array',
            'children_education' => 'required|array',
            'children_professional' => 'required|array',
            // 'children_phone' => 'required|array', // Uncomment if needed
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Please Enter The Required Fields',
                'success' => false,
                'errors' => $validator->messages(),
            ]);
        }

        // Find the active member by mem_id
        $member = MembershipModel::where('mem_id', $mem_id)
            ->where('status', 'active')
            ->first();

        if (!$member) {
            return response()->json([
                'message' => 'Member Not Found',
                'success' => false,
            ]);
        }

        // Check if 'children_name' exists in the request
        if ($request->has('children_name')) {
            // Iterate over the array of children's details
            for ($i = 0; $i < count($request->children_name); $i++) {
                // Generate a unique children ID
                $children_id = $this->generateChildrenID($member->first_name);

                // Create a new ChildrenModel instance and save it
                $children = new ChildrenModel([
                    'mem_id' => $member->mem_id,
                    'children_id' => $children_id,
                    'children_name' => $request->children_name[$i],
                    'relation' => $request->relation[$i],
                    'children_dob' => $request->children_dob[$i],
                    'children_education' => $request->children_education[$i],
                    'children_professional' => $request->children_professional[$i], // Uncomment if needed
                    'status' => 'active',
                    'created_at' => now(),
                ]);
                $children->save();
            }
        }

        return response()->json([
            'message' => 'Children details added successfully.',
            'success' => true,
        ]);
    }



    public function get_mem_profile(Request $request, $mem_token)
    {
        // Retrieve profile based on mem_token and approval_status
        $profile = MembershipModel::where('mem_token', $mem_token)
            ->where('approval_status', 'approved')
            ->select('mem_id', 'first_name', 'last_name', 'phone', 'member_image')
            ->first(); // Get the first record instead of get()

        // Check if profile is found
        if (!$profile) {
            $success['message'] = 'Member Not Found';
            $success['success'] = false; // Set success to false
            return response()->json($success);
        } else {
            $result = [
                "mem_id" => $profile->mem_id,
                "first_name" => $profile->first_name,
                "last_name" => $profile->last_name,
                "phone" => $profile->phone,
                "member_image" => !empty($profile->member_image)
                    ? 'E:/wamp64/www/cvmv/cvmv/uploads/images/member_images/' . $profile->member_image
                    : ''
            ];

            return response()->json(["result" => $result]);
        }
    }



    public function get_my_membership(Request $request, $mem_token)
    {
        // First query to check if the member exists
        $member = DB::table('cvmv_membership')
            ->where('mem_token', '=', $mem_token)
            ->first();

        // Check if the member exists
        if (empty($member)) {
            $fail = [
                'message' => 'Member Not Found',
                'success' => false
            ];
            return response()->json($fail);
        } else {
            // Second query to check if the member is approved
            $member = DB::table('cvmv_membership')
                ->where('mem_token', '=', $mem_token)
                ->where('approval_status', '=', 'approved')
                ->first();

            // Check if the member is approved
            if (empty($member)) {
                $fail = [
                    'message' => 'Member is in pending list',
                    'success' => false
                ];
                return response()->json($fail);
            } else {
                // Query to get children details
                $children = DB::table('cvmv_children')
                    ->where('mem_id', '=', $member->mem_id)
                    ->select('children_name', 'relation', 'children_dob', 'children_education', 'children_professional')
                    ->get();

                // Prepare the result array
                $result = [
                    "mem_id" => $member->mem_id,
                    "first_name" => $member->first_name,
                    "last_name" => $member->last_name,
                    "phone" => $member->phone,
                    "gender" => $member->gender,
                    "date_of_birth" => $member->date_of_birth,
                    "father_name" => $member->father_name,
                    "mother_name" => $member->mother_name,
                    "qualification" => $member->qualification,
                    "job_designation" => $member->job_designation,
                    "marriage_date" => $member->marriage_date,
                    "blood_group" => $member->blood_group,
                    "vagaiyara" => $member->vagaiyara,
                    "kula_deivam" => $member->kula_deivam,
                    "temple_place" => $member->temple_place,
                    "district" => $member->district,
                    "native_place" => $member->native_place,
                    "address" => $member->address,
                    "wife_name" => $member->wife_name,
                    "wife_dob" => $member->wife_dob,
                    "wife_phone" => $member->wife_phone,
                    "wife_qualification" => $member->wife_qualification,
                    "wife_birth_place" => $member->wife_birth_place,
                    "wife_job_designation" => $member->wife_job_designation,
                    "wife_district" => $member->wife_district,
                    "status" => $member->status,
                    "approval_status" => $member->approval_status,
                    "member_image" => $member->member_image,
                    "created_at" => $member->created_at
                ];

                // Add children details to the result
                $result['children'] = $children;

                // Check and set member_image path
                $member_image = !empty($member->member_image)
                    ? 'E:\wamp64\www\cvmv\cvmv\uploads\images\member_images/' . $member->member_image
                    : '';

                $result['member_image'] = $member_image;

                return response()->json(["result" => $result]);
            }
        }
    }



    public function get_member_details(Request $request, $mem_id)
    {
        // First query to check if the member exists
        $member = DB::table('cvmv_membership')
            ->where('mem_id', '=', $mem_id)
            ->first();

        // Check if the member exists
        if (empty($member)) {
            $fail = [
                'message' => 'Member Not Found',
                'success' => false
            ];
            return response()->json($fail);
        } else {
            // Second query to check if the member is approved
            $member = DB::table('cvmv_membership')
                ->where('mem_id', '=', $mem_id)
                ->where('approval_status', '=', 'approved')
                ->first();

            // Check if the member is approved
            if (empty($member)) {
                $fail = [
                    'message' => 'Member is in pending list',
                    'success' => false
                ];
                return response()->json($fail);
            } else {
                // Query to get children details
                $children = DB::table('cvmv_children')
                    ->where('mem_id', '=', $member->mem_id)
                    ->select('children_name', 'relation', 'children_dob', 'children_education', 'children_professional')
                    ->get();

                // Prepare the result array
                $result = [
                    "mem_id" => $member->mem_id,
                    "first_name" => $member->first_name,
                    "last_name" => $member->last_name,
                    "phone" => $member->phone,
                    "gender" => $member->gender,
                    "date_of_birth" => $member->date_of_birth,
                    "father_name" => $member->father_name,
                    "mother_name" => $member->mother_name,
                    "qualification" => $member->qualification,
                    "job_designation" => $member->job_designation,
                    "marriage_date" => $member->marriage_date,
                    "blood_group" => $member->blood_group,
                    "vagaiyara" => $member->vagaiyara,
                    "kula_deivam" => $member->kula_deivam,
                    "temple_place" => $member->temple_place,
                    "district" => $member->district,
                    "native_place" => $member->native_place,
                    "address" => $member->address,
                    "wife_name" => $member->wife_name,
                    "wife_dob" => $member->wife_dob,
                    "wife_phone" => $member->wife_phone,
                    "wife_qualification" => $member->wife_qualification,
                    "wife_birth_place" => $member->wife_birth_place,
                    "wife_job_designation" => $member->wife_job_designation,
                    "wife_district" => $member->wife_district,
                    "status" => $member->status,
                    "approval_status" => $member->approval_status,
                    "member_image" => $member->member_image,
                    "created_at" => $member->created_at
                ];

                // Add children details to the result
                $result['children'] = $children;

                // Check and set member_image path
                $member_image = !empty($member->member_image)
                    ? 'E:\wamp64\www\cvmv\cvmv\uploads\images\member_images/' . $member->member_image
                    : '';

                $result['member_image'] = $member_image;

                return response()->json(["result" => $result]);
            }
        }
    }


    public function get_all_members(Request $request)
    {
        try {
            // Get pagination parameters from the request
            $limit = $request->input('limit', 20); // Default limit 20 members per page
            $offset = $request->input('offset', 0); // Default offset is 0

            // Fetch approved members with pagination
            $members = DB::table('cvmv_membership')
                ->where('approval_status', 'approved')
                ->offset($offset)
                ->limit($limit)
                ->get();

            // Check if records exist
            if ($members->isEmpty()) {
                return response()->json([
                    'message' => 'No approved members found',
                    'success' => false
                ], 404);
            }

            // Prepare the results array
            $results = [];

            foreach ($members as $member) {
                $member_image = !empty($member->member_image)
                    ? 'https://tabsquareinfotech.com/App/Abinesh_be_work/tsit_cvmv/uploads/images/member_images/' . $member->member_image
                    : '';

                // Fetch children details for the current member
                $children = DB::table('cvmv_children')
                    ->where('mem_id', $member->mem_id)
                    ->get(['children_name', 'relation', 'children_dob', 'children_education', 'children_professional' ]);

               // $location_id = DistrictModel::where('location_id', $member->)

                // Add member and children details to the results array
                $results[] = [
                    "mem_id" => $member->mem_id,
                    "first_name" => $member->first_name,
                    "last_name" => $member->last_name,
                    "phone" => $member->phone,
                    "gender" => $member->gender,
                    "date_of_birth" => $member->date_of_birth,
                    "father_name" => $member->father_name,
                    "mother_name" => $member->mother_name,
                    "qualification" => $member->qualification,
                    "job_designation" => $member->job_designation,
                    "marriage_date" => $member->marriage_date,
                    "blood_group" => $member->blood_group,
                    "vagaiyara" => $member->vagaiyara,
                    "kula_deivam" => $member->kula_deivam,
                    "temple_place" => $member->temple_place,
                    "district" => $member->district,
                    "native_place" => $member->native_place,
                    "address" => $member->address,
                    "wife_name" => $member->wife_name,
                    "wife_dob" => $member->wife_dob,
                    "wife_phone" => $member->wife_phone,
                    "wife_qualification" => $member->wife_qualification,
                    "wife_birth_place" => $member->wife_birth_place,
                    "wife_job_designation" => $member->wife_job_designation,
                    "wife_district" => $member->wife_district,
                    "status" => $member->status,
                    "approval_status" => $member->approval_status,
                    "member_image" => $member_image,
                    "created_at" => $member->created_at,
                    "children" => $children
                ];
            }

            // Return the fetched data as a JSON response
            return response()->json([
                'message' => 'Records fetched successfully',
                'success' => true,
                'data' => $results,
                'offset' => $offset + count($results), // Provide next offset for pagination
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching records',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }






    // public function get_all_members(Request $request)
    // {
    //     try {
    //         // Fetch all approved members
    //         $members = DB::table('cvmv_membership')
    //             ->where('approval_status', 'approved')
    //             ->get();

    //         // Check if records exist
    //         if ($members->isEmpty()) {
    //             return response()->json([
    //                 'message' => 'Member is in pending list',
    //                 'success' => false
    //             ], 404);
    //         }

    //         // Prepare the results array
    //         $results = [];

    //         foreach ($members as $member) {
    //             $member_image = !empty($member->member_image)
    //                 ? 'E:\wamp64\www\cvmv\cvmv\uploads\images\member_images/' . $member->member_image
    //                 : '';

    //             $results[] = [
    //                 "mem_id" => $member->mem_id,
    //                 "first_name" => $member->first_name,
    //                 "last_name" => $member->last_name,
    //                 "phone" => $member->phone,
    //                 "gender" => $member->gender,
    //                 "date_of_birth" => $member->date_of_birth,
    //                 "father_name" => $member->father_name,
    //                 "mother_name" => $member->mother_name,
    //                 "qualification" => $member->qualification,
    //                 "job_designation" => $member->job_designation,
    //                 "marriage_date" => $member->marriage_date,
    //                 "blood_group" => $member->blood_group,
    //                 "vagaiyara" => $member->vagaiyara,
    //                 "kula_deivam" => $member->kula_deivam,
    //                 "temple_place" => $member->temple_place,
    //                 "district" => $member->district,
    //                 "native_place" => $member->native_place,
    //                 "address" => $member->address,
    //                 "wife_name" => $member->wife_name,
    //                 "wife_dob" => $member->wife_dob,
    //                 "wife_phone" => $member->wife_phone,
    //                 "wife_qualification" => $member->wife_qualification,
    //                 "wife_birth_place" => $member->wife_birth_place,
    //                 "wife_job_designation" => $member->wife_job_designation,
    //                 "wife_district" => $member->wife_district,
    //                 "status" => $member->status,
    //                 "approval_status" => $member->approval_status,
    //                 "member_image" => $member_image,
    //                 "created_at" => $member->created_at
    //             ];
    //         }

    //         // Return the fetched data as a JSON response
    //         return response()->json([
    //             'message' => 'Records fetched successfully',
    //             'success' => true,
    //             'data' => $results
    //         ], 200);
    //     } catch (\Exception $e) {
    //         // Handle any errors
    //         return response()->json([
    //             'message' => 'An error occurred while fetching records',
    //             'success' => false,
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }





    // public function edit_member_details(Request $request, $mem_token){
    //     $member = DB::table('cvmv_membership')
    //     ->where('mem_token', '=', $mem_token)
    //     ->where('status', '=', 'active')
    //     ->where('approval_status', '=', 'approved')
    //     ->first();
    //     if(!$member){
    //         $fail['message'] = 'Authendication Failed';
    //         $fail['success'] = false;
    //     } else {
    //        // date_default_timezone_set('Asia/Calcutta');
    //      // $current_date = now();
    //         $first_name = $request->input('first_name');
    //         $last_name = $request->input('last_name');
    //         $phone = $request->input('phone');
    //         $gender = $request->input('gender');
    //         $date_of_birth = $request->input('date_of_birth');
    //         $father_name = $request->input('father_name');
    //         $mother_name = $request->input('mother_name');
    //         $qualification = $request->input('qualification');
    //         $job_designation = $request->input('job_designation');
    //         $marriage_date = $request->input('marriage_date');
    //         $blood_group = $request->input('blood_group');
    //         $vagaiyara = $request->input('vagaiyara');
    //         $kula_deivam = $request->input('kula_deivam');
    //         $temple_place = $request->input('temple_place');
    //         $district = $request->input('district');
    //         $native_place = $request->input('native_place');
    //         $address = $request->input('address');
    //         $wife_name = $request->input('wife_name');
    //         $wife_dob = $request->input('wife_dob');
    //         $wife_phone = $request->input('wife_phone');
    //         $wife_qualification = $request->input('wife_qualification');
    //         $wife_birth_place = $request->input('wife_birth_place');
    //         $wife_job_designation = $request->input('wife_job_designation');
    //         $wife_district = $request->input('wife_district');
    //         $member_image = $request->file('image');

    //         if(!empty($member_image)) {
    //             $time = time();
    //             $img_id = (date("dmyHis", $time));
    //             $path = 'MEMIMG' . "$img_id" . ".png";
    //             $directoryPath = public_path('../uploads/images/member_images/');

    //             \Illuminate\Support\Facades\File::makeDirectory($directoryPath, $mode = 0777, true, true);
    //             $member_image->move($directoryPath, $path);

    //         } else {
    //             $path = '';
    //         }

    //         $update = DB::table('cvmv_membership')
    //         ->where('mem_token',$mem_token)
    //         ->where('status','active')
    //         ->update([
    //             "first_name" => $first_name,
    //             "last_name" => $last_name,
    //             "phone" => $phone,
    //             "gender" => $gender,
    //             "date_of_birth" => $date_of_birth,
    //             "father_name" => $father_name,
    //             "mother_name" => $mother_name,
    //             "qualification" => $qualification,
    //             "job_designation" => $job_designation,
    //             "marriage_date" => $marriage_date,
    //             "blood_group" => $blood_group,
    //             "vagaiyara" => $vagaiyara,
    //             "kula_deivam" => $kula_deivam,
    //             "temple_place" => $temple_place,
    //             "district" => $district,
    //             "native_place" => $native_place,
    //             "address" => $address,
    //             "wife_name" => $wife_name,
    //             "wife_dob" => $wife_dob,
    //             "wife_phone" => $wife_phone,
    //             "wife_qualification" => $wife_qualification,
    //             "wife_birth_place" => $wife_birth_place,
    //             "wife_job_designation" => $wife_job_designation,
    //             "wife_district" => $wife_district,
    //             'member_image' => $member->member_image,
    //             "updated_at" => now()
    //         ]);
    //         if(!$update){
    //             $success['message'] = 'Membership not updated';
    //             $success['success'] = false;
    //             return response()->json($success);
    //         } else {
    //             $success['message'] = 'Membership updated successfully';
    //             $success['success'] = true;
    //             return response()->json($success);
    //         }
    //     }
    // }



    public function edit_member_details(Request $request, $mem_token)
    {
        $member = DB::table('cvmv_membership')
            ->where('mem_token', '=', $mem_token)
            ->where('status', '=', 'active')
            ->where('approval_status', '=', 'approved')
            ->first();

        if (!$member) {
            $fail['message'] = 'Authentication Failed';
            $fail['success'] = false;
            return response()->json($fail);
        } else {

            $current_date = now();

            $first_name = $request->input('first_name');
            $last_name = $request->input('last_name');
            $phone = $request->input('phone');
            $gender = $request->input('gender');
            $date_of_birth = $request->input('date_of_birth');
            $father_name = $request->input('father_name');
            $mother_name = $request->input('mother_name');
            $qualification = $request->input('qualification');
            $job_designation = $request->input('job_designation');
            $marriage_date = $request->input('marriage_date');
            $blood_group = $request->input('blood_group');
            $vagaiyara = $request->input('vagaiyara');
            $kula_deivam = $request->input('kula_deivam');
            $temple_place = $request->input('temple_place');
            $district = $request->input('district');
            $native_place = $request->input('native_place');
            $address = $request->input('address');
            $wife_name = $request->input('wife_name');
            $wife_dob = $request->input('wife_dob');
            $wife_phone = $request->input('wife_phone');
            $wife_qualification = $request->input('wife_qualification');
            $wife_birth_place = $request->input('wife_birth_place');
            $wife_job_designation = $request->input('wife_job_designation');
            $wife_district = $request->input('wife_district');

            $path = $member->member_image; // Default to current image path

            if ($request->hasFile('member_image')) {
                $member_image = $request->file('member_image');
                $time = time();
                $img_id = date("dmyHis", $time);
                $mem_id = $member->mem_id;
                $path = $mem_id . "." . $member_image->getClientOriginalExtension();
                $directoryPath = public_path('../uploads/images/member_images/');

                // Ensure the directory exists
                if (!\Illuminate\Support\Facades\File::exists($directoryPath)) {
                    \Illuminate\Support\Facades\File::makeDirectory($directoryPath, 0777, true, true);
                }

                $member_image->move($directoryPath, $path);
            }

            $update = DB::table('cvmv_membership')
                ->where('mem_token', $mem_token)
                ->where('status', 'active')
                ->update([
                    "first_name" => $first_name,
                    "last_name" => $last_name,
                    "phone" => $phone,
                    "gender" => $gender,
                    "date_of_birth" => $date_of_birth,
                    "father_name" => $father_name,
                    "mother_name" => $mother_name,
                    "qualification" => $qualification,
                    "job_designation" => $job_designation,
                    "marriage_date" => $marriage_date,
                    "blood_group" => $blood_group,
                    "vagaiyara" => $vagaiyara,
                    "kula_deivam" => $kula_deivam,
                    "temple_place" => $temple_place,
                    "district" => $district,
                    "native_place" => $native_place,
                    "address" => $address,
                    "wife_name" => $wife_name,
                    "wife_dob" => $wife_dob,
                    "wife_phone" => $wife_phone,
                    "wife_qualification" => $wife_qualification,
                    "wife_birth_place" => $wife_birth_place,
                    "wife_job_designation" => $wife_job_designation,
                    "wife_district" => $wife_district,
                    'member_image' => $path, // Update with new path
                    "updated_at" => $current_date
                ]);

            if (!$update) {
                $success['message'] = 'Membership not updated';
                $success['success'] = false;
            } else {
                $success['message'] = 'Membership updated successfully';
                $success['success'] = true;
            }
            return response()->json($success);
        }
    }




    public function mem_approval(Request $request, $mem_id)
    {

        // Retrieve the franchise record
        $mem = DB::table('cvmv_membership')
            ->where('mem_id', '=', $mem_id)
            // ->where('approval_status', '=', 'pending')
            ->first();

        if (!$mem) {
            $success['message'] = "Member not found";
            $success['success'] = false;
            return response()->json($success);
        }

        // Determine the new status
        $new_status = $mem->approval_status == 'pending' ? 'approved' : 'pending';

        // Update the status
        DB::table('cvmv_membership')
            ->where('mem_id', $mem_id)
            //  ->where('approval_status', '=', $menu_category_id)
            ->update(['approval_status' => $new_status]);

        $success['message'] = "Member Approved successfully";
        $success['new_status'] = $new_status;
        $success['success'] = true;
        return response()->json($success);
    }


    public function mem_pending_list()
    {
        try {
            // Fetch all records from the phonepe_response table
            $responses = MembershipModel::where('approval_status', '=', 'pending')->get();

            // Check if records exist
            if ($responses->isEmpty()) {
                return response()->json([
                    'message' => 'No records found',
                    'success' => false
                ], 404);
            }

            // Return the fetched data as a JSON response
            return response()->json([
                'message' => 'Records fetched successfully',
                'success' => true,
                'data' => $responses
            ], 200);
        } catch (\Exception $e) {
            // Handle any errors
            return response()->json([
                'message' => 'An error occurred while fetching records',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function delete_member(Request $request, $mem_id)
    {
        $del_mem = DB::table('cvmv_membership')
            ->where('mem_id', $mem_id)
            ->where('status', '=', 'active')
            ->delete();

        if (!$del_mem) {
            $success['message'] = 'Memder Not Deleted';
            $success['success'] = false;
            return response()->json($success);
        } else {
            $success['message'] = 'Member Deleted Succesfully';
            $success['success'] = true;
            return response()->json($success);
        }
    }




    public function mem_response(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'response' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Decode the Base64 response
        $encodedResponse = $request->input('response');
        $decodedResponse = base64_decode($encodedResponse);

        if ($decodedResponse === false) {
            return response()->json(['error' => 'Base64 decode failed'], 400);
        }

        // Decode the JSON response
        $response = json_decode($decodedResponse, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON received'], 400);
        }

        // Save the raw response in the database
        $mem_phonepe = new MemPhonepeModel();
        $mem_phonepe->response =  json_encode($response);
        $mem_phonepe->created_at = now();
        $mem_phonepe->save();

        if (!$mem_phonepe) {
            return response()->json([
                'message' => "Response not added successfully",
                'success' => false,
            ]);
        }

        // Extract necessary fields from the response
        $success = $response['success'];
        $code = $response['code'];
        $message = $response['message'];
        $merchantId = $response['data']['merchantId'];
        $merchantTransactionId = $response['data']['merchantTransactionId'];
        $transactionId = $response['data']['transactionId'];
        $amount = $response['data']['amount'];
        $state = $response['data']['state'];
        $responseCode = $response['data']['responseCode'];
        $type = $response['data']['paymentInstrument']['type'] ?? '';
        $utr = $response['data']['paymentInstrument']['utr'] ?? null;
        $card_type = $response['data']['paymentInstrument']['cardType'] ?? null;
        $pgTransactionId = $response['data']['paymentInstrument']['pgTransactionId'] ?? null;
        $bankTransactionId = $response['data']['paymentInstrument']['bankTransactionId'] ?? null;
        $pgAuthorizationCode = $response['data']['paymentInstrument']['pgAuthorizationCode'] ?? null;
        $arn = $response['data']['paymentInstrument']['arn'] ?? null;
        $bankId = $response['data']['paymentInstrument']['bankId'] ?? null;
        $pgServiceTransactionId = $response['data']['pgServiceTransactionId'] ?? null;


        $successTF = $success ? 'True' : 'False';
        $amountpaisa = $amount / 100;

        // Save the decoded response in the new table
        $phonepe_response = new MemPhonepeResponseModel();
        $phonepe_response->success = $successTF;
        $phonepe_response->code = $code;
        $phonepe_response->message = $message;
        $phonepe_response->merchant_id = $merchantId;
        $phonepe_response->merchant_transaction_id = $merchantTransactionId;
        $phonepe_response->transaction_id = $transactionId;
        $phonepe_response->amount = $amountpaisa;
        $phonepe_response->state = $state;
        $phonepe_response->response_code = $responseCode;
        $phonepe_response->type = $type;
        $phonepe_response->utr = $utr;
        $phonepe_response->card_type = $card_type;
        $phonepe_response->pg_transation_id = $pgTransactionId;
        $phonepe_response->bank_transaction_id = $bankTransactionId;
        $phonepe_response->pg_authorization_code = $pgAuthorizationCode;
        $phonepe_response->arn = $arn;
        $phonepe_response->bank_id = $bankId;
        $phonepe_response->pg_service_transaction_id = $pgServiceTransactionId;
        $phonepe_response->status = 'active';
        $phonepe_response->created_at = now();
        $phonepe_response->updated_at = now();
        $phonepe_response->save();

        if (!$phonepe_response) {
            return response()->json([
                'message' => "Response not added successfully to the new table",
                'success' => false,
            ]);
        }

        $transactionId = $response['data']['transactionId'] ?? null;
        $state = $response['data']['state'] ?? null;
        $merchantTransactionId = $response['data']['merchantTransactionId'] ?? null;
        $success = $response['success'] ?? false;

        if (!$transactionId || !$responseCode || !$merchantTransactionId) {
            return response()->json([
                'message' => "Required fields not found in the response",
                'success' => false,
            ], 400);
        }

        // Determine payment status based on success field
        //  $paymentStatus = $success ? 'success' : 'failed';

        // Determine order status based on payment status
        //  $orderStatus = $success ? 'order placed' : 'order not placed';


        // Validate the necessary ID (merchantTransactionId)
        $merchant_transaction_id = $merchantTransactionId;

        // Update payment and order details
        $current_time = now();
        $current_date = $current_time->toDateString();

        $updatePaymentStatus = DB::table('cvmv_memberrship')
            ->where('merchant_transaction_id', '=', $merchant_transaction_id)
            ->whereDate('date', $current_date)
            ->where('status', '=', 'active')
            ->update([
                'transaction_id' => $transactionId,
                //'transaction_status' => $state,
                'payment_status' => $state
            ]);


        if ($updatePaymentStatus === 0) {
            return response()->json([
                'message' => "No changes made or updates not successful",
                'success' => false
            ]);
        }

        return response()->json([
            'message' => "Response added and details updated successfully",
            'success' => true,
        ]);
    }

    public function edit_children(Request $request, $children_id)
    {
        $children = ChildrenModel::where('children_id', $children_id)
            ->where('status', 'active')
            ->first();

        if (!$children) {
            return response()->json(['message' => 'Child not found or not active'], 404);
        } else {
            $children_name = $request->input('children_name');
            $relation = $request->input('relation');
            $children_dob = $request->input('children_dob');
            $children_education = $request->input('children_education');
            $children_professional = $request->input('children_professional');

            $update = ChildrenModel::where('children_id', $children_id)
                ->where('status', 'active')
                ->update([
                    "children_name" => $children_name,
                    "relation" => $relation,
                    "children_dob" => $children_dob,
                    "children_education" => $children_education,
                    "children_professional" => $children_professional,
                    "updated_at" => now()
                ]);

            if (!$update) {
                $success['message'] = "ChildrenDetail Not Updated";
                $success['message'] = false;
                return response()->json($success);
            } else {
                $success['message'] = "ChildrenDetail Updated Successfully";
                $success['message'] = true;
                return response()->json($success);
            }
        }
    }



    public function get_native_members(Request $request, $location_id)
    {
        $nativePlaceEng = $request->input('native_place_eng');
        $nativePlaceTam = $request->input('native_place_tam');

        // Query to find members based on either English or Tamil native place
        $members = DB::table('cvmv_membership')
            ->where(function ($query) use ($nativePlaceEng, $nativePlaceTam) {
                $query->where('native_place', $nativePlaceEng)
                      ->orWhere('native_place', $nativePlaceTam);
            })
            ->where('approval_status', 'approved')
            ->get();
            if ($members->isEmpty()) {
                return response()->json([
                    'message' => 'No members found for the specified native place',
                    'success' => false
                ], 404);
            }
            // Prepare the result
            $result = [];
            foreach ($members as $member) {
                $member_image = !empty($member->member_image)
                    ? 'E:\wamp64\www\cvmv\cvmv\uploads\images\member_images/' . $member->member_image
                    : '';
                $result[] = [
                    "mem_id" => $member->mem_id,
                    "first_name" => $member->first_name,
                    "last_name" => $member->last_name,
                    "phone" => $member->phone,
                    "gender" => $member->gender,
                    "date_of_birth" => $member->date_of_birth,
                    "father_name" => $member->father_name,
                    "mother_name" => $member->mother_name,
                    "qualification" => $member->qualification,
                    "job_designation" => $member->job_designation,
                    "marriage_date" => $member->marriage_date,
                    "blood_group" => $member->blood_group,
                    "vagaiyara" => $member->vagaiyara,
                    "kula_deivam" => $member->kula_deivam,
                    "temple_place" => $member->temple_place,
                    "district" => $member->district,
                    "native_place" => $member->native_place,
                    "address" => $member->address,
                    "wife_name" => $member->wife_name,
                    "wife_dob" => $member->wife_dob,
                    "wife_phone" => $member->wife_phone,
                    "wife_qualification" => $member->wife_qualification,
                    "wife_birth_place" => $member->wife_birth_place,
                    "wife_job_designation" => $member->wife_job_designation,
                    "wife_district" => $member->wife_district,
                    "status" => $member->status,
                    "approval_status" => $member->approval_status,
                    "member_image" => $member_image,
                    "created_at" => $member->created_at
                    // Add other relevant member details here
                ];
            }

            // Return the result
            return response()->json([
                'message' => 'Members fetched successfully',
                'success' => true,
                'members' => $result
            ], 200);
        }
    }

