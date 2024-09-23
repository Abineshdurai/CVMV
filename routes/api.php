<?php

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;

// /*
// |--------------------------------------------------------------------------
// | API Routes
// |--------------------------------------------------------------------------
// |
// | Here is where you can register API routes for your application. These
// | routes are loaded by the RouteServiceProvider and all of them will
// | be assigned to the "api" middleware group. Make something great!
// |
// */

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MembershipController;


define("onemincryptKey", "tabsquare2022");
define("oneminsecretKey", "tabsquareinfo2021");
function pass_encrypt($string)
{
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $key = hash('sha256', oneminsecretKey); // hash
    $iv = substr(hash('sha256', onemincryptKey), 0, 16); // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
    $output = base64_encode($output);
    return $output;
}
function pass_decrypt($string)
{
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $key = hash('sha256', oneminsecretKey); // hash
    $iv = substr(hash('sha256', onemincryptKey), 0, 16); // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    return $output;
}


// define("onemincryptKey", "tabsquare2022");
// define("oneminsecretKey", "tabsquareinfo2021");

// if (!function_exists('pass_encrypt')) {
//     function pass_encrypt($string) {
//         $output = false;
//         $encrypt_method = "AES-256-CBC";
//         $key = hash('sha256', oneminsecretKey); // hash
//         $iv = substr(hash('sha256', onemincryptKey), 0, 16); // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
//         $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
//         $output = base64_encode($output);
//         return $output;
//     }
// }

// if (!function_exists('pass_decrypt')) {
//     function pass_decrypt($string) {
//         $output = false;
//         $encrypt_method = "AES-256-CBC";
//         $key = hash('sha256', oneminsecretKey); // hash
//         $iv = substr(hash('sha256', onemincryptKey), 0, 16); // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
//         $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
//         return $output;
//     }
// }




date_default_timezone_set('Asia/Calcutta');
$time = time();
$key = "TabSquare";
$id = (date("dmyHis", $time));
define("APP_KEY", $key);
function tokenKey($session_uid)
{
    $key = md5(APP_KEY . $session_uid);
    return hash('sha256', $key . $_SERVER['REMOTE_ADDR']);
}



//     date_default_timezone_set('Asia/Calcutta');
// $time = time();
// $key = "TabSquare";
// $id = date("dmyHis", $time);
// define("APP_KEY", $key);

// if (!function_exists('tokenKey')) {
//     function tokenKey($session_uid) {
//         $key = md5(APP_KEY . $session_uid);
//         return hash('sha256', $key . $_SERVER['REMOTE_ADDR']);
//     }
// }




Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Route::middleware('auth:sanctum')->get('/admin', function (Request $request) {
//     return $request->admin();
// });


// ----CVMV Membership
Route::post('/sendmobileOTP', 'App\Http\Controllers\MembershipController@sendmobileOTP');
Route::post('/verifyOTP', 'App\Http\Controllers\MembershipController@verifyOTP');
Route::post('/validateToken', 'App\Http\Controllers\MembershipController@validateToken');
Route::post('/create_personal_membership', 'App\Http\Controllers\MembershipController@create_personal_membership');
Route::post('/get_all_members', 'App\Http\Controllers\MembershipController@get_all_members');
Route::post('/get_mem_profile/{mem_token}', 'App\Http\Controllers\MembershipController@get_mem_profile');
Route::post('/get_my_membership/{mem_token}', 'App\Http\Controllers\MembershipController@get_my_membership');
Route::post('/get_member_details/{mem_id}', 'App\Http\Controllers\MembershipController@get_member_details');
Route::post('/edit_member_details/{mem_token}', 'App\Http\Controllers\MembershipController@edit_member_details');
Route::post('/mem_approval/{mem_id}', 'App\Http\Controllers\MembershipController@mem_approval');
Route::post('/mem_pending_list', 'App\Http\Controllers\MembershipController@mem_pending_list');
Route::post('/delete_member/{mem_id}', 'App\Http\Controllers\MembershipController@delete_member');
Route::post('/get_native_members/{location_id}', 'App\Http\Controllers\MembershipController@get_native_members');
Route::post('/edit_children/{childern_id}', 'App\Http\Controllers\MembershipController@edit_children');
Route::post('/temp_create_personal_membership', 'App\Http\Controllers\MembershipController@temp_create_personal_membership');

// --------> CVMV Childern
Route::post('/Tsit_Cvmv_Add_Children/{mem_id}', 'App\Http\Controllers\MembershipController@Tsit_Cvmv_Add_Children');



// -------> CVMV District
Route::post('/add_dist', 'App\Http\Controllers\DistrictController@add_dist');
Route::post('/get_dist', 'App\Http\Controllers\DistrictController@get_dist');
Route::post('/get_native/{district}', 'App\Http\Controllers\DistrictController@get_native');


// -------> CVMV Matrimony
Route::post('/Tsit_Cvmv_Create_Matri_User', 'App\Http\Controllers\MatrimonyController@Tsit_Cvmv_Create_Matri_User');
Route::post('/Tsit_Cvmv_Create_Matrimony', 'App\Http\Controllers\MatrimonyController@Tsit_Cvmv_Create_Matrimony');
Route::post('/Tsit_Cvmv_Get_Matri_User_Details/{matri_token}', 'App\Http\Controllers\MatrimonyController@Tsit_Cvmv_Get_Matri_User_Details');
Route::post('/Tsit_Cvmv_Get_All_Matri_Details', 'App\Http\Controllers\MatrimonyController@Tsit_Cvmv_Get_All_Matri_Details');
Route::post('/Tsit_Cvmv_Edit_Matri_User/{matri_token}', 'App\Http\Controllers\MatrimonyController@Tsit_Cvmv_Edit_Matri_User');
Route::post('/Tsit_Cvmv_Edit_Matrimony/{matri_id}', 'App\Http\Controllers\MatrimonyController@Tsit_Cvmv_Edit_Matrimony');
Route::post('/Tsit_Cvmv_Edit_Matri_images/{matri_img_id}', 'App\Http\Controllers\MatrimonyController@Tsit_Cvmv_Edit_Matri_images');
Route::post('/Tsit_Cvmv_Get_Matri_User_Profile/{matri_token}', 'App\Http\Controllers\MatrimonyController@Tsit_Cvmv_Get_Matri_User_Profile');
Route::post('/Tsit_Cvmv_Create_Matrimony_temp', 'App\Http\Controllers\MatrimonyController@Tsit_Cvmv_Create_Matrimony_temp');
Route::post('/Tsit_Cvmv_Filter_Matrimony/{filter}', 'App\Http\Controllers\MatrimonyController@Tsit_Cvmv_Filter_Matrimony');


// -----> CVMV Version
Route::post('/Tsit_Cvmv_Add_Version', 'App\Http\Controllers\VersionController@Tsit_Cvmv_Add_Version');
Route::post('/Tsit_Cvmv_Check_Version/{version_name}/{version_code}', 'App\Http\Controllers\VersionController@Tsit_Cvmv_Check_Version');
