<?php

namespace App\Http\Controllers;

use App\Models\MatrimonyModel;
use App\Models\MatriUserModel;
use App\Models\MatrimonyImagesModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;


class MatrimonyController extends Controller
{
    public function Tsit_Cvmv_Create_Matri_User(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'matri_user_name' => 'required',
            'matri_user_proof' => 'required',
            'matri_user_phone' => 'required|numeric|digits:10|unique:cvmv_matri_user,matri_user_phone',
            'matri_user_address' => 'required',
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 200);
        }

        date_default_timezone_set('Asia/Calcutta');
        $matri_user_id = "CMU" . now()->format('YmdHis');
        $matri_token = tokenKey($matri_user_id);

        $matri_user = new MatriUserModel([
            'matri_user_id' => $matri_user_id,
            'matri_token' => $matri_token,
            'matri_user_name' => $request->matri_user_name,
            'matri_user_proof' => $request->matri_user_proof,
            'matri_user_address' => $request->matri_user_address,
            'matri_user_phone' => $request->matri_user_phone,
            'status' => 'active',
            'cteated_at' => now(),

        ]);
        $matri_user->save();

        return response()->json([
            'success' => true,
            'matri_token' => $matri_token,
            'matri_user_id' => $matri_user_id,
            'message' => 'Matrimony User created Successfully'
        ]);
    }


    public function Tsit_Cvmv_Create_Matrimony_temp(Request $request) //-------> Temp
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'matri_user_id' => 'required',
            'name' => 'required',
            'm_status' => 'required',
            'email' => 'required|email',
            'date_of_birth' => 'required|date',
            'blood_group' => 'required',
            'qualification' => 'required',
            'kula_deivam' => 'required',
            'temple_place' => 'required',
            'm_height' => 'required',
            'm_weight' => 'required',
            'district' => 'required',
            'native_place' => 'required',
            'address' => 'required',
            'gender' => 'required',
            'job_designation' => 'required',
            'job_location' => 'required',
            'job_annual_income' => 'required',
            'father_name' => 'required',
            'father_occupation' => 'required',
            'mother_name' => 'required',
            'mother_occupation' => 'required',
            'j_rasi' => 'required',
            'j_nakshatra' => 'required',
            'j_dhosam' => 'required',
            'horoscope_attach' => 'required|file', // Ensure horoscope_attach is a file
            'no_of_brothers' => 'required|integer',
            'no_of_sisters' => 'required|integer',
            'm_count' => 'required',
            'matri_image' => 'nullable|array', // Make it nullable if images are optional
            'matri_image.*' => 'string' // Ensure each image is a string
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400); // Return error status 400
        }

        date_default_timezone_set('Asia/Calcutta');
        $latestId = MatrimonyModel::max('id') + 1; // Fetch the latest ID and increment by 1
        $matri_id = 'MT' . now()->format('dmy') . $latestId;

        // Handle horoscope attachment upload
        if ($request->hasFile('horoscope_attach')) {
            $horoscope_attach = $request->file('horoscope_attach');
            $horoextension = $horoscope_attach->getClientOriginalExtension();
            $horoscope = "HORO" . now()->format('YmdHsi') . "." . $horoextension;
            $directoryPath = public_path('uploads/images/horoscope_attach/');
            \Illuminate\Support\Facades\File::ensureDirectoryExists($directoryPath, 0777, true);
            $horoscope_attach->move($directoryPath, $horoscope);
        } else {
            $horoscope = '';
        }

        $matri = new MatrimonyModel([
            'matri_user_id' => $request->matri_user_id,
            'matri_id' => $matri_id,
            'name' => $request->name,
            'm_status' => $request->m_status,
            'email' => $request->email,
            'date_of_birth' => $request->date_of_birth,
            'blood_group' => $request->blood_group,
            'qualification' => $request->qualification,
            'kula_deivam' => $request->kula_deivam,
            'temple_place' => $request->temple_place,
            'm_height' => $request->m_height,
            'm_weight' => $request->m_weight,
            'm_color' => $request->m_color,
            'district' => $request->district,
            'native_place' => $request->native_place,
            'address' => $request->address,
            'gender' => $request->gender,
            'job_designation' => $request->job_designation,
            'job_location' => $request->job_location,
            'job_annual_income' => $request->job_annual_income,
            'father_name' => $request->father_name,
            'father_occupation' => $request->father_occupation,
            'father_number' => $request->father_number,
            'mother_name' => $request->mother_name,
            'mother_occupation' => $request->mother_occupation,
            'mother_number' => $request->mother_number,
            'j_rasi' => $request->j_rasi,
            'j_nakshatra' => $request->j_nakshatra,
            'j_dhosam' => $request->j_dhosam,
            'horoscope_attach' => $horoscope,
            'no_of_brothers' => $request->no_of_brothers,
            'no_of_sisters' => $request->no_of_sisters,
            'm_count' => $request->m_count,
            'mat_status' => 'active',
            'last_login' => now(),
            'created_at' => now(),
        ]);
        $matri->save();

        if (!$matri) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add matrimony'
            ], 500); // Return error status 500
        }

        // Handle multiple images upload
        if ($request->has('matri_image')) {
            foreach ($request->input('matri_image') as $matri_image) {
                // Remove the extension from the image name
                $filenameWithoutExtension = pathinfo($matri_image, PATHINFO_FILENAME);

                $matri_images = new MatrimonyImagesModel([
                    'matri_id' => $matri_id,
                    'matri_img_id' => $filenameWithoutExtension, // Use filename without extension
                    'matri_image' => $matri_image, // This might be a URL or base64 string
                    'status' => 'active',
                    'created_at' => now()
                ]);
                $matri_images->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Matrimony added successfully'
        ], 200); // Return success status 200
    }





    public function Tsit_Cvmv_Create_Matrimony(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'matri_user_id' => 'required',
            //  'profile_for' => 'required',
            'name' => 'required',
            'phone' => 'required|numeric|digits:10|unique:cvmv_matrimony,phone',
            'm_status' => 'required',
            'email' => 'required|email',
            'date_of_birth' => 'required|date',
            'blood_group' => 'required',
            'qualification' => 'required',
            'kula_deivam' => 'required',
            'temple_place' => 'required',
            // 'birth_time' => 'required',
            // 'birth_place' => 'required',
            'm_height' => 'required',
            'm_weight' => 'required',
            //  'm_color' => 'required',
            'district' => 'required',
            'native_place' => 'required', // Removed the space after 'native_place'
            'address' => 'required',
            'gender' => 'required',
            'job_designation' => 'required',
            'job_location' => 'required',
            // 'company' => 'required',
            'job_annual_income' => 'required',
            'father_name' => 'required',
            'father_occupation' => 'required',
            //father_number
            'mother_name' => 'required',
            'mother_occupation' => 'required',
            //mother_number
            'j_rasi' => 'required',
            'j_nakshatra' => 'required',
            'j_dhosam' => 'required',
            'horoscope_attach' => 'required', // Ensure the file is valid
            // 'part_age' => 'required',
            // 'part_height' => 'required',
            // 'part_complex' => 'required',
            // 'part_marital_status' => 'required',
            // 'part_religion_com' => 'required',
            // 'part_language_know' => 'required',
            // 'part_district' => 'required',
            // 'part_state' => 'required',
            // 'part_country' => 'required',
            // 'other_details' => 'required',
            'no_of_brothers' => 'required|integer',
            'no_of_sisters' => 'required|integer',
            'm_count' => 'required|integer',
            'matri_image' => 'required|array', // Ensure matri_image is an array for multiple uploads
            // 'matri_image.*' => 'file|mimes:jpg,png,jpeg|max:2048' // Validate each image file
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 200);
        }

        date_default_timezone_set('Asia/Calcutta');
        $matri_id = 'MT' . now()->format('YmdHis');

        // Handle horoscope attachment upload
        if ($request->hasFile('horoscope_attach')) {
            $horoscope_attach = $request->file('horoscope_attach');
            $horoextension = $horoscope_attach->getClientOriginalExtension();
            $horoscope = "HORO" . now()->format('YmdHsi') . "." . $horoextension;
            $directoryPath = public_path('uploads/images/horoscope_attach/');
            \Illuminate\Support\Facades\File::ensureDirectoryExists($directoryPath, 0777, true);
            $horoscope_attach->move($directoryPath, $horoscope);
        } else {
            $horoscope = '';
        }

        // if ($request->hasFile('horoscope_attach')) {
        //     $horoscope_attach = $request->file('horoscope_attach');
        //     $horoextension = strtolower($horoscope_attach->getClientOriginalExtension()); // Ensuring the extension is lowercase
        //     $horoscope = "HORO" . now()->format('YmdHis') . "." . $horoextension; // Changed 'Hsi' to 'His'
        //     $directoryPath = public_path('uploads/images/horoscope_attach/');

        //     // Ensure the directory exists
        //     File::ensureDirectoryExists($directoryPath);

        //     // Move the uploaded file to the target directory
        //     $horoscope_attach->move($directoryPath, $horoscope);
        // } else {
        //     $horoscope = '';
        // }

        // Create new matrimony entry
        $matri = new MatrimonyModel([
            'matri_user_id' => $request->matri_user_id,
            'matri_id' => $matri_id,
            // 'profile_for' => $request->profile_for,
            'name' => $request->name,
            'phone' => $request->phone,
            'm_status' => $request->m_status,
            'email' => $request->email,
            'date_of_birth' => $request->date_of_birth,
            'blood_group' => $request->blood_group,
            'qualification' => $request->qualification,
            'kula_deivam' => $request->kula_deivam,
            'temple_place' => $request->temple_place,
            // 'birth_time' => $request->birth_time,
            //  'birth_place' => $request->birth_place,
            'm_height' => $request->m_height,
            'm_weight' => $request->m_weight,
            'm_color' => $request->m_color,
            'district' => $request->district,
            'native_place' => $request->native_place,
            'address' => $request->address,
            'gender' => $request->gender,
            'job_designation' => $request->job_designation,
            'job_location' => $request->job_location,
            // 'company' => $request->company,
            'job_annual_income' => $request->job_annual_income,
            'father_name' => $request->father_name,
            'father_occupation' => $request->father_occupation,
            'father_number' => $request->father_number,
            'mother_name' => $request->mother_name,
            'mother_occupation' => $request->mother_occupation,
            'mother_number' => $request->mother_number,
            'j_rasi' => $request->j_rasi,
            'j_nakshatra' => $request->j_nakshatra,
            'j_dhosam' => $request->j_dhosam,
            'horoscope_attach' => $horoscope,
            // 'part_age' => $request->part_age,
            // 'part_height' => $request->part_height,
            // 'part_complex' => $request->part_complex,
            // 'part_marital_status' => $request->part_marital_status,
            // 'part_religion_com' => $request->part_religion_com,
            // 'part_language_know' => $request->part_language_know,
            // 'part_district' => $request->part_district,
            // 'part_state' => $request->part_state,
            // 'part_country' => $request->part_country,
            // 'other_details' => $request->other_details,
            'no_of_brothers' => $request->no_of_brothers,
            'no_of_sisters' => $request->no_of_sisters,
            'm_count' => $request->m_count,
            'mat_status' => 'active',
            'last_login' => now(),
            'created_at' => now(),
        ]);
        $matri->save();

        if (!$matri) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add matrimony'
            ]);
        }

        // Handle multiple images upload
        // if ($request->has('matri_image')) {
        //     foreach ($request->file('matri_image') as $matri_image) {
        //         $matri_img_id =  MatrimonyImagesModel::generateMatImage(); //'MATIMG' . now()->format('YmdHsi');
        //         $matri_img_extension = $matri_image->getClientOriginalExtension();
        //         $matri_img_file = $matri_img_id . "." . $matri_img_extension;
        //         $path = public_path('uploads/images/matrimony_images/');
        //         \Illuminate\Support\Facades\File::ensureDirectoryExists($path, 0777, true);
        //         $matri_image->move($path, $matri_img_file);

        //         // Save each image to the database
        //         $matri_images = new MatrimonyImagesModel([
        //             'matri_id' => $matri_id,
        //             'matri_img_id' => $matri_img_id,
        //             'matri_image' => $matri_img_file,
        //             'status' => 'active',
        //             'created_at' => now()
        //         ]);
        //         $matri_images->save();
        //     }
        // }
        if ($request->hasFile('matri_images')) {
            $images = $request->file('matri_images');
            $matri_img_ids = $request->input('matri_img_ids', []); // Default to an empty array if not set

            foreach ($images as $key => $image) {
                // Check if a matri_img_id exists; if not, generate a new one
                $matri_img_id = isset($matri_img_ids[$key]) ? $matri_img_ids[$key] : MatrimonyImagesModel::generateMatImage();

                // Create a unique image name
                $imageName = uniqid() . "." . $image->getClientOriginalExtension();
                $imagePath = public_path('uploads/images/matrimony_images/');
                \Illuminate\Support\Facades\File::ensureDirectoryExists($imagePath, 0777, true);
                $image->move($imagePath, $imageName);

                // If matri_img_id is set, update existing image
                if (in_array($matri_img_id, $matri_img_ids)) {
                    $existingImage = MatrimonyImagesModel::where('matri_id', $matri_id)
                        ->where('matri_img_id', $matri_img_id)
                        ->first();

                    if ($existingImage) {
                        // Delete the old image from storage
                        \Illuminate\Support\Facades\File::delete($imagePath . $existingImage->matri_image);

                        // Update the image record with the new image
                        $existingImage->update([
                            'matri_image' => $imageName,
                        ]);
                    } else {
                        return response()->json([
                            'message' => 'Image not found for update',
                            'success' => false
                        ]);
                    }
                } else {
                    // If no matri_img_id is set, add a new image record
                    MatrimonyImagesModel::create([
                        'matri_id' => $matri_id,
                        'matri_img_id' => $matri_img_id, // Use the generated or existing matri_img_id
                        'matri_image' => $imageName,
                    ]);
                }
            }
        }


        return response()->json([
            'success' => true,
            'message' => 'Matrimony added successfully'
        ]);
    }

    public function Tsit_Cvmv_Get_Matri_User_Profile(Request $request, $matri_token)
    {
        // Retrieve the user with the given matri_token and status 'active'
        $matri_user = MatriUserModel::where('matri_token', $matri_token)
            ->where('status', 'active')
            ->select(
                'matri_user_id',
                'matri_user_name',
                'matri_user_proof',
                'matri_user_address',
                'matri_user_phone'
            )
            ->first();

        // Check if the user exists
        if (!$matri_user) {
            return response()->json([
                'message' => 'Matrimony User Not Found',
                'success' => false
            ]);
        }

        // Prepare the user details
        $matri_user_details = [
            "matri_user_id" => $matri_user->matri_user_id,
            "matri_user_name" => $matri_user->matri_user_name,
            "matri_user_proof" => $matri_user->matri_user_proof,
            "matri_user_address" => $matri_user->matri_user_address,
            "matri_user_phone" => $matri_user->matri_user_phone,
        ];
        return response()->json([
            'success' => true,
            'matri_profile' => $matri_user_details,
            'message' => 'Matri User Detils Retrived successfully'
        ]);
    }



    public function Tsit_Cvmv_Get_Matri_User_Details(Request $request, $matri_token)
    {
        // Retrieve the user with the given matri_token and status 'active'
        $matri_user = MatriUserModel::where('matri_token', $matri_token)
            ->where('status', 'active')
            ->select(
                'matri_user_id',
                'matri_user_name',
                'matri_user_proof',
                'matri_user_address',
                'matri_user_phone'
            )
            ->first();

        // Check if the user exists
        if (!$matri_user) {
            return response()->json([
                'message' => 'Matrimony User Not Found',
                'success' => false
            ]);
        }

        $matrimony_records = MatrimonyModel::where('matri_user_id', $matri_user->matri_user_id)
            ->where('mat_status', 'active')
            ->select(
                'matri_id',
                'name',
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
                'j_rasi',
                'j_nakshatra',
                'j_dhosam',
                'horoscope_attach',
                'no_of_brothers',
                'no_of_sisters',
                'm_count'
            )
            ->get();

            if(!$matrimony_records)
            {
                $success ['message'] = 'Add the Details';
                $success ['success'] = false;
                return response()->json([$success]);
            } else {

        $matrimony_details_with_images = $matrimony_records->map(function ($matrimony) {
            $matrimony_images = MatrimonyImagesModel::where('matri_id', $matrimony->matri_id)
                ->where('status', 'active')
                ->select(
                    'matri_img_id',
                    'matri_image'
                )
                ->get();

            error_log('Images for matri_id ' . $matrimony->matri_id . ': ' . $matrimony_images->count());

            $matri_images = $matrimony_images->map(function ($image) {
                return [
                    'matri_img_id' => $image->matri_img_id,
                    'matri_image' => !empty($image->matri_image)
                        //? 'https://tabsquareinfotech.com/App/Abinesh_be_work/tsit_cvmv/public/uploads/images/matrimony_images/' . $image->matri_image
                        ? asset('uploads/images/matrimony_images/') . $image->matri_image
                        : ''
                ];
            })->toArray();

            $horoscope_attach_path = !empty($matrimony->horoscope_attach)
                //  ? 'https://tabsquareinfotech.com/App/Abinesh_be_work/tsit_cvmv/public/uploads/images/horoscope_attach/' . $matrimony->horoscope_attach
                ? asset('uploads/images/horoscope_attach/') . $matrimony->horoscope_attach
                : '';


            return [
                'matrimony_details' => $matrimony,
                'horoscope_attach' => $horoscope_attach_path,
                'matrimony_images' => $matri_images

            ];
        });

        return response()->json([
            "matri_user_details" => $matri_user,
            "matrimony_records" => $matrimony_details_with_images,
        ]);
    }
    }




    public function Tsit_Cvmv_Get_All_Matri_Details(Request $request)
    {


        $matrimony_records = MatrimonyModel:: //where('matri_user_id', $matri_user->matri_user_id)
            where('mat_status', 'active')
            ->select(
                'matri_id',
                //  'profile_for',
                'name',
                // 'phone',
                'm_status',
                'email',
                'date_of_birth',
                'blood_group',
                'qualification',
                'kula_deivam',
                'temple_place',
                // 'birth_time',
                // 'birth_place',
                'm_height',
                'm_weight',
                'm_color',
                'district',
                'native_place',
                'address',
                'gender',
                'job_designation',
                'job_location',
                // 'company',
                'job_annual_income',
                'father_name',
                'father_occupation',
                'father_number',
                'mother_name',
                'mother_occupation',
                'mother_number',
                'j_rasi',
                'j_nakshatra',
                'j_dhosam',
                'horoscope_attach',
                // 'part_age',
                // 'part_height',
                // 'part_complex',
                // 'part_marital_status',
                // 'part_religion_com',
                // 'part_language_know',
                // 'part_district',
                // 'part_state',
                // 'part_country',
                // 'other_details',
                'no_of_brothers',
                'no_of_sisters',
                'm_count'
            )
            ->get();

        // Prepare matrimony details with images and horoscope attach path
        $matrimony_details_with_images = $matrimony_records->map(function ($matrimony) {
            $matrimony_images = MatrimonyImagesModel::where('matri_id', $matrimony->matri_id)
                ->where('status', 'active')
                ->select(
                    'matri_img_id',
                    'matri_image'
                )
                ->get();

            $matri_images = $matrimony_images->map(function ($image) {
                return [
                    'matri_img_id' => $image->matri_img_id,
                    'matri_image' => !empty($image->matri_image)
                        ? public_path('uploads/images/matri_image/') . $image->matri_image
                        // ? 'E:/wamp64/www/cvmv/cvmv/uploads/images/matri_image/' . $image->matri_image
                        : ''
                ];
            });
            // $matri_images = !empty($matrimony_images->matri_image)
            // ? 'E:/wamp64/www/cvmv/cvmv/uploads/images/matrimony_images/' . $matrimony_images->matri_image
            // : '';

            $horoscope_attach_path = !empty($matrimony->horoscope_attach)
                ? public_path('uploads/images/horoscope_attach/') . $matrimony->horoscope_attach
                // ? 'E:/wamp64/www/cvmv/cvmv/uploads/images/horoscope_attach/' . $matrimony->horoscope_attach
                : '';

            // Return matrimony details along with images and horoscope attach path
            return [
                'matrimony_details' => $matrimony,
                'horoscope_attach' => $horoscope_attach_path,
                'matrimony_images' => $matri_images
            ];
        });

        // Return the response as a JSON
        return response()->json([
            //"matri_user_details" => $matri_user,
            "matrimony_records" => $matrimony_details_with_images
        ]);
    }


    public function Tsit_Cvmv_Edit_Matri_User(Request $request, $matri_token)
    {
        $validator = Validator::make($request->all(), [
            'matri_user_name' => 'required',
            'matri_user_proof' => 'required',
            //'matri_user_phone' => 'required|numeric|digits:10|unique:cvmv_matri_user,matri_user_phone',
            'matri_user_address' => 'required',
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 200);
        }

        $matriUser = MatriUserModel::where('matri_token', $matri_token)
            ->where('status', 'active')
            ->first();

        if (!$matriUser) {
            return response()->json([
                'message' => 'User Not Found',
                'success' => false
            ]);
        } else {
            $update = MatriUserModel::where('matri_token', $matri_token)
                ->where('status', 'active')
                ->update([
                    'matri_user_name' => $request->input('matri_user_name'),
                    'matri_user_proof' => $request->input('matri_user_proof'),
                    // 'matri_user_phone' => $request->input('matri_user_phone'),
                    'matri_user_address' => $request->input('matri_user_address'),
                    'updated_at' => now()
                ]);
            if (!$update) {
                return response()->json([
                    'message' => 'User Details Not Updated',
                    'success' => false
                ]);
            } else {
                return response()->json([
                    'message' => 'User Details Updated',
                    'success' => true
                ]);
            }
        }
    }


    // public function Tsit_Cvmv_Edit_Matrimony(Request $request, $matri_id)
    // {

    //     $matrimony = MatrimonyModel::where('matri_id', $matri_id)
    //         ->where('mat_status', 'active')
    //         ->first();

    //     if (!$matrimony) {
    //         return response()->json([
    //             'message' => 'Person Not Found',
    //             'success' => false
    //         ]);
    //     } else {
    //         $name = $request->input('name');
    //         $m_status = $request->input('m_status');
    //         $email = $request->input('email');
    //         $date_of_birth = $request->input('date_of_birth');
    //         $blood_group = $request->input('blood_group');
    //         $qualification = $request->input('qualification');
    //         $kula_deivam = $request->input('kula_deivam');
    //         $temple_place = $request->input('temple_place');
    //         $m_height = $request->input('m_height');
    //         $m_weight = $request->input('m_weight');
    //         $m_color = $request->input('m_color');
    //         $district = $request->input('district');
    //         $native_place = $request->input('native_place');
    //         $address = $request->input('address');
    //         $gender = $request->input('gender');
    //         $job_designation = $request->input('job_designation');
    //         $job_location = $request->input('job_location');
    //         $job_annual_income = $request->input('job_annual_income');
    //         $father_name = $request->input('father_name');
    //         $father_occupation = $request->input('father_occupation');
    //         $father_number = $request->input('father_number');
    //         $mother_name = $request->input('mother_name');
    //         $mother_occupation = $request->input('mother_occupation');
    //         $mother_number = $request->input('mother_number');
    //         $j_rasi = $request->input('j_rasi');
    //         $j_nakshatra = $request->input('j_nakshatra');
    //         $j_dhosam = $request->input('j_dhosam');
    //         $horoscope_attach = $request->file('horoscope_attach');
    //         $no_of_brothers = $request->input('no_of_brothers');
    //         $no_of_sisters = $request->input('no_of_sisters');
    //         $m_count = $request->input('m_count');


    //         if ($request->hasFile('horoscope_attach')) {
    //             $horoscope_attach = $request->file('horoscope_attach');
    //             $horoscope = "HORO" . now()->format('YmdHsi') . "." . $horoscope_attach->getClientOriginalExtension();
    //             $directoryPath = public_path('uploads/images/horoscope_attach/');
    //             \Illuminate\Support\Facades\File::ensureDirectoryExists($directoryPath, 0777, true);
    //             $horoscope_attach->move($directoryPath, $horoscope);
    //         } else {
    //             $horoscope = '';
    //         }

    //         $update = MatrimonyModel::where('matri_id', $matri_id)
    //             ->where('mat_status', 'active')
    //             ->update([
    //                 'name' => $name,
    //                 'm_status' => $m_status,
    //                 'email' => $email,
    //                 'date_of_birth' => $date_of_birth,
    //                 'blood_group' => $blood_group,
    //                 'qualification' => $qualification,
    //                 'kula_deivam' => $kula_deivam,
    //                 'temple_place' => $temple_place,
    //                 'm_height' => $m_height,
    //                 'm_weight' => $m_weight,
    //                 'm_color' => $m_color,
    //                 'district' => $district,
    //                 'native_place' => $native_place,
    //                 'address' => $address,
    //                 'gender' => $gender,
    //                 'job_designation' => $job_designation,
    //                 'job_location' => $job_location,
    //                 'job_annual_income' => $job_annual_income,
    //                 'father_name' => $father_name,
    //                 'father_occupation' => $father_occupation,
    //                 'father_number' => $father_number,
    //                 'mother_name' => $mother_name,
    //                 'mother_occupation' => $mother_occupation,
    //                 'mother_number' => $mother_number,
    //                 'j_rasi' => $j_rasi,
    //                 'j_nakshatra' => $j_nakshatra,
    //                 'j_dhosam' => $j_dhosam,
    //                 'horoscope_attach' => $horoscope,
    //                 'no_of_brothers' => $no_of_brothers,
    //                 'no_of_sisters' => $no_of_sisters,
    //                 'm_count' => $m_count,
    //             ]);

    //         if (!$update) {
    //             $success['message'] = 'Matrimony not updated';
    //             $success['success'] = false;
    //             return response()->json($success);
    //         } else {
    //             $success['message'] = 'Matrimony updated successfully';
    //             $success['success'] = true;
    //             return response()->json($success);
    //         }

    //     }
    // }

    public function Tsit_Cvmv_Edit_Matrimony(Request $request, $matri_id)
    {
        $matrimony = MatrimonyModel::where('matri_id', $matri_id)
            ->where('mat_status', 'active')
            ->first();

        if (!$matrimony) {
            return response()->json([
                'message' => 'Person Not Found',
                'success' => false
            ]);
        }

        // Update Matrimony Details
        $matrimony->update([
            'name' => $request->input('name'),
            'm_status' => $request->input('m_status'),
            'email' => $request->input('email'),
            'date_of_birth' => $request->input('date_of_birth'),
            'blood_group' => $request->input('blood_group'),
            'qualification' => $request->input('qualification'),
            'kula_deivam' => $request->input('kula_deivam'),
            'temple_place' => $request->input('temple_place'),
            'm_height' => $request->input('m_height'),
            'm_weight' => $request->input('m_weight'),
            'm_color' => $request->input('m_color'),
            'district' => $request->input('district'),
            'native_place' => $request->input('native_place'),
            'address' => $request->input('address'),
            'gender' => $request->input('gender'),
            'job_designation' => $request->input('job_designation'),
            'job_location' => $request->input('job_location'),
            'job_annual_income' => $request->input('job_annual_income'),
            'father_name' => $request->input('father_name'),
            'father_occupation' => $request->input('father_occupation'),
            'father_number' => $request->input('father_number'),
            'mother_name' => $request->input('mother_name'),
            'mother_occupation' => $request->input('mother_occupation'),
            'mother_number' => $request->input('mother_number'),
            'j_rasi' => $request->input('j_rasi'),
            'j_nakshatra' => $request->input('j_nakshatra'),
            'j_dhosam' => $request->input('j_dhosam'),
            'no_of_brothers' => $request->input('no_of_brothers'),
            'no_of_sisters' => $request->input('no_of_sisters'),
            'm_count' => $request->input('m_count'),
        ]);

        // Update Horoscope Attachment
        if ($request->hasFile('horoscope_attach')) {
            $horoscope_attach = $request->file('horoscope_attach');
            $horoscope = "HORO" . now()->format('YmdHsi') . "." . $horoscope_attach->getClientOriginalExtension();
            $directoryPath = public_path('uploads/images/horoscope_attach/');
            \Illuminate\Support\Facades\File::ensureDirectoryExists($directoryPath, 0777, true);
            $horoscope_attach->move($directoryPath, $horoscope);

            // Delete the old horoscope if it exists
            \Illuminate\Support\Facades\File::delete($directoryPath . $matrimony->horoscope_attach);

            // Update the horoscope_attach field
            $matrimony->update(['horoscope_attach' => $horoscope]);
        }

        // Replace Multiple Matrimony Images
        if ($request->hasFile('matri_images')) {
            $images = $request->file('matri_images');
            $matri_img_ids = $request->input('matri_img_ids'); // array of matri_img_id

            foreach ($images as $key => $image) {
                // Check if a matri_img_id exists; if not, generate a new one
                $matri_img_id = isset($matri_img_ids[$key]) ? $matri_img_ids[$key] : MatrimonyImagesModel::generateMatImage();

                // Create a unique image name
                $imageName = uniqid() . "." . $image->getClientOriginalExtension();
                $imagePath = public_path('uploads/images/matrimony_images/');
                \Illuminate\Support\Facades\File::ensureDirectoryExists($imagePath, 0777, true);
                $image->move($imagePath, $imageName);

                // If matri_img_id is set, update existing image
                if (isset($matri_img_ids[$key]) && in_array($matri_img_id, $matri_img_ids)) {
                    $existingImage = MatrimonyImagesModel::where('matri_id', $matri_id)
                        ->where('matri_img_id', $matri_img_id)
                        ->first();

                    if ($existingImage) {
                        // Delete the old image from storage
                        \Illuminate\Support\Facades\File::delete($imagePath . $existingImage->matri_image);

                        // Update the image record with the new image
                        $existingImage->update([
                            'matri_image' => $imageName,
                        ]);
                    } else {
                        return response()->json([
                            'message' => 'Image not found for update',
                            'success' => false
                        ]);
                    }
                } else {
                    // If no matri_img_id is set, add a new image record
                    MatrimonyImagesModel::create([
                        'matri_id' => $matri_id,
                        'matri_img_id' => $matri_img_id, // Use the generated or existing matri_img_id
                        'matri_image' => $imageName,
                        'status' => 'active'
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Matrimony updated successfully',
            'success' => true
        ]);
    }



    // public function Tsit_Cvmv_Edit_Matrimony(Request $request, $matri_id)
    // {
    //     $matrimony = MatrimonyModel::where('matri_id', $matri_id)
    //         ->where('mat_status', 'active')
    //         ->first();

    //     if (!$matrimony) {
    //         return response()->json([
    //             'message' => 'Person Not Found',
    //             'success' => false
    //         ]);
    //     }

    //     // Update Matrimony Details
    //     $matrimony->update([
    //         'name' => $request->input('name'),
    //         'm_status' => $request->input('m_status'),
    //         'email' => $request->input('email'),
    //         'date_of_birth' => $request->input('date_of_birth'),
    //         'blood_group' => $request->input('blood_group'),
    //         'qualification' => $request->input('qualification'),
    //         'kula_deivam' => $request->input('kula_deivam'),
    //         'temple_place' => $request->input('temple_place'),
    //         'm_height' => $request->input('m_height'),
    //         'm_weight' => $request->input('m_weight'),
    //         'm_color' => $request->input('m_color'),
    //         'district' => $request->input('district'),
    //         'native_place' => $request->input('native_place'),
    //         'address' => $request->input('address'),
    //         'gender' => $request->input('gender'),
    //         'job_designation' => $request->input('job_designation'),
    //         'job_location' => $request->input('job_location'),
    //         'job_annual_income' => $request->input('job_annual_income'),
    //         'father_name' => $request->input('father_name'),
    //         'father_occupation' => $request->input('father_occupation'),
    //         'father_number' => $request->input('father_number'),
    //         'mother_name' => $request->input('mother_name'),
    //         'mother_occupation' => $request->input('mother_occupation'),
    //         'mother_number' => $request->input('mother_number'),
    //         'j_rasi' => $request->input('j_rasi'),
    //         'j_nakshatra' => $request->input('j_nakshatra'),
    //         'j_dhosam' => $request->input('j_dhosam'),
    //         'no_of_brothers' => $request->input('no_of_brothers'),
    //         'no_of_sisters' => $request->input('no_of_sisters'),
    //         'm_count' => $request->input('m_count'),
    //     ]);

    //     // Update Horoscope Attachment
    //     if ($request->hasFile('horoscope_attach')) {
    //         $horoscope_attach = $request->file('horoscope_attach');
    //         $horoscope = "HORO" . now()->format('YmdHsi') . "." . $horoscope_attach->getClientOriginalExtension();
    //         $directoryPath = public_path('uploads/images/horoscope_attach/');
    //         \Illuminate\Support\Facades\File::ensureDirectoryExists($directoryPath, 0777, true);
    //         $horoscope_attach->move($directoryPath, $horoscope);

    //         // Delete the old horoscope if it exists
    //         \Illuminate\Support\Facades\File::delete($directoryPath . $matrimony->horoscope_attach);

    //         // Update the horoscope_attach field
    //         $matrimony->update(['horoscope_attach' => $horoscope]);
    //     }

    //     // Replace Multiple Matrimony Images
    //     if ($request->hasFile('matri_images')) {
    //         $images = $request->file('matri_images');
    //         $matri_img_ids = $request->input('matri_img_ids'); // array of matri_img_id

    //         foreach ($images as $key => $image) {
    //             $matri_img_id = $matri_img_ids[$key]; // Corresponding matri_img_id for each image
    //             $imageName = "IMG" . now()->format('YmdHsi') . uniqid() . "." . $image->getClientOriginalExtension();
    //             $imagePath = public_path('uploads/images/matrimony_images/');
    //             \Illuminate\Support\Facades\File::ensureDirectoryExists($imagePath, 0777, true);
    //             $image->move($imagePath, $imageName);

    //             // Update all matching images instead of using ->first()
    //             MatrimonyImagesModel::where('matri_id', $matri_id)
    //                 ->where('matri_img_id', $matri_img_id)
    //                 ->update([
    //                     'matri_image' => $imageName,
    //                     'updated_at' => now()
    //                 ]);
    //         }
    //     }

    //     return response()->json([
    //         'message' => 'Matrimony updated successfully',
    //         'success' => true
    //     ]);
    // }



    public function Tsit_Cvmv_Edit_Matri_images(Request $request, $matri_img_id)
    {
        // Fetch the active matrimony image record
        $matri_image_record = MatrimonyImagesModel::where('matri_img_id', $matri_img_id)
            ->where('status', 'active')
            ->first();

        // Check if the image record exists
        if (!$matri_image_record) {
            return response()->json([
                'message' => 'Image Not Found',
                'success' => false
            ]);
        }

        // Initialize the path variable
        $path = $matri_image_record->matri_image;

        // Check if the request has a file for 'matri_image'
        if ($request->hasFile('matri_image')) {
            // Get the uploaded file
            $uploaded_image = $request->file('matri_image');
            $time = time();
            $img_id = date("dmyHis", $time);

            // Construct the new file path
            $new_filename = $matri_img_id . "." . $uploaded_image->getClientOriginalExtension();
            $directoryPath = public_path('../uploads/images/matrimony_images/');

            // Ensure the directory exists
            if (!\Illuminate\Support\Facades\File::exists($directoryPath)) {
                \Illuminate\Support\Facades\File::makeDirectory($directoryPath, 0777, true, true);
            }

            // Move the uploaded file to the target directory
            $uploaded_image->move($directoryPath, $new_filename);

            // Update the path variable with the new filename
            $path = $new_filename;
        }

        // Update the database record
        $update = MatrimonyImagesModel::where('matri_img_id', $matri_img_id)
            ->where('status', 'active')
            ->update([
                'matri_image' => $path,  // Correct field name for database
                'updated_at' => now()
            ]);

        // Prepare the response message
        $success = [
            'message' => $update ? 'Image updated successfully' : 'Image not updated',
            'success' => $update
        ];

        // Return the JSON response
        return response()->json($success);
    }




    // public function Tsit_Cvmv_Filter_Matrimony(Request $request, $filter)
    // {
    //     // Get the offset and limit from the request (default to 0 and 20 if not provided)
    //     $offset = $request->input('offset', 0);
    //     $limit = $request->input('limit', 20);

    //     // Define the columns to search for the filter
    //     $columns = [
    //         'name',
    //         'm_status',
    //         'date_of_birth',
    //         'blood_group',
    //         'qualification',
    //         'kula_deivam',
    //         'temple_place',
    //         'm_height',
    //         'm_weight',
    //         'm_color',
    //         'district',
    //         'native_place',
    //         'address',
    //         'gender',
    //         'job_designation',
    //         'job_location',
    //         'job_annual_income',
    //         'j_rasi',
    //         'j_nakshatra',
    //         'j_dhosam',
    //         'no_of_brothers',
    //         'no_of_sisters',
    //         'm_count'
    //     ];

    //     // Build the query to fetch records based on the filter across multiple columns
    //     $query = MatrimonyModel::where('mat_status', 'active');

    //     foreach ($columns as $column) {
    //         $query->orWhere($column, 'like', '%' . $filter . '%');
    //     }

    //     // Execute the query with pagination
    //     $matrimony_records = $query
    //         ->select(
    //             'matri_user_id',
    //             'matri_id',
    //             'name',
    //             'm_status',
    //             'email',
    //             'date_of_birth',
    //             'blood_group',
    //             'qualification',
    //             'kula_deivam',
    //             'temple_place',
    //             'm_height',
    //             'm_weight',
    //             'm_color',
    //             'district',
    //             'native_place',
    //             'address',
    //             'gender',
    //             'job_designation',
    //             'job_location',
    //             'job_annual_income',
    //             'father_name',
    //             'father_occupation',
    //             'father_number',
    //             'mother_name',
    //             'mother_occupation',
    //             'mother_number',
    //             'j_rasi',
    //             'j_nakshatra',
    //             'j_dhosam',
    //             'horoscope_attach',
    //             'no_of_brothers',
    //             'no_of_sisters',
    //             'm_count'
    //         )
    //         ->offset($offset)   // Set the offset
    //         ->limit($limit)     // Set the limit
    //         ->get();

    //     // Prepare matrimony details with images and horoscope attach path
    //     $matrimony_details_with_images = $matrimony_records->map(function ($matrimony) {
    //         $matrimony_images = MatrimonyImagesModel::where('matri_id', $matrimony->matri_id)
    //             ->where('status', 'active')
    //             ->select(
    //                 'matri_img_id',
    //                 'matri_image'
    //             )
    //             ->get();

    //         $matri_images = $matrimony_images->map(function ($image) {
    //             return [
    //                 'matri_img_id' => $image->matri_img_id,
    //                 'matri_image' => !empty($image->matri_image)
    //                     ? 'https://tabsquareinfotech.com/App/Abinesh_be_work/tsit_cvmv/public/uploads/images/matrimony_images/' . $image->matri_image
    //                     : ''
    //             ];
    //         });

    //         $horoscope_attach_path = !empty($matrimony->horoscope_attach)
    //             ? 'https://tabsquareinfotech.com/App/Abinesh_be_work/tsit_cvmv/public/uploads/images/horoscope_attach/' . $matrimony->horoscope_attach
    //             : '';

    //         // Return matrimony details along with images and horoscope attach path
    //         return [
    //             'matrimony_details' => $matrimony,
    //             'horoscope_attach' => $horoscope_attach_path,
    //             'matrimony_images' => $matri_images
    //         ];
    //     });

    //     // Return the response with matrimony details
    //     return response()->json([
    //         "matrimony_records" => $matrimony_details_with_images,
    //         "has_more_data" => $matrimony_records->count() == $limit  // Check if more records exist
    //     ]);
    // }



    public function Tsit_Cvmv_Search_Matrimony(Request $request)
{
    // Get the offset and limit from the request (default to 0 and 20 if not provided)
    $offset = $request->input('offset', 0);
    $limit = $request->input('limit', 10);

    // Get the search query from the request
    $query = $request->input('search');

    // Build the query to fetch records based on the search filter across multiple columns
    $matrimonysQuery = MatrimonyModel::where('mat_status', 'active');

    if (!empty($query)) {
        $matrimonysQuery->where(function($q) use ($query) {
            $q->where('name', 'LIKE', "$query%")
              ->orWhere('matri_id', 'LIKE', "%$query");
        });
    }

    // Execute the query with pagination
    $matrimony_records = $matrimonysQuery
        ->select(
            'matri_user_id',
            'matri_id',
            'name',
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
            'j_rasi',
            'j_nakshatra',
            'j_dhosam',
            'horoscope_attach',
            'no_of_brothers',
            'no_of_sisters',
            'm_count'
        )
        ->offset($offset)   // Set the offset
        ->limit($limit)     // Set the limit
        ->get();

    // Prepare matrimony details with images and horoscope attachment paths
    $matrimony_details_with_images = $matrimony_records->map(function ($matrimony) {
        $matrimony_images = MatrimonyImagesModel::where('matri_id', $matrimony->matri_id)
            ->where('status', 'active')
            ->select(
                'matri_img_id',
                'matri_image'
            )
            ->get();

        $matri_images = $matrimony_images->map(function ($image) {
            return [
                'matri_img_id' => $image->matri_img_id,
                'matri_image' => !empty($image->matri_image)
                    ? 'https://tabsquareinfotech.com/App/Abinesh_be_work/tsit_cvmv/public/uploads/images/matrimony_images/' . $image->matri_image
                    : ''
            ];
        });

        $horoscope_attach_path = !empty($matrimony->horoscope_attach)
            ? 'https://tabsquareinfotech.com/App/Abinesh_be_work/tsit_cvmv/public/uploads/images/horoscope_attach/' . $matrimony->horoscope_attach
            : '';

        // Return matrimony details along with images and horoscope attach path
        return [
            'matrimony_details' => $matrimony,
            'horoscope_attach' => $horoscope_attach_path,
            'matrimony_images' => $matri_images
        ];
    });

    // Return the response with matrimony details
    return response()->json([
        "matrimony_records" => $matrimony_details_with_images,
        "has_more_data" => $matrimony_records->count() == $limit  // Check if more records exist
    ]);
}



}
