<?php

namespace App\Http\Controllers;

use App\Models\EmailVerificationModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class EmailVerificationController extends Controller
{

    public function Tsit_Cvmv_Sent_EmailOTP(Request $request)
    {
        // Retrieve the MSG91 Auth Key from environment variables
        $authKey = env('MSG91_AUTH_KEY');

        // Validate the email input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            // Return validation error messages if validation fails
            return response()->json($validator->messages(), 200);
        } else {
            // Set a specific OTP for a specific email
            $specificEmail = "bpmdev24@gmail.com"; // The specific email to not send OTP
            if ($request->input('email') === $specificEmail) {
                $otp = 1234;
                $sendEmail = false; // Do not send email for the specific email address
            } else {
                // Generate a random 4-digit OTP for other emails
                $otp = rand(1000, 9999);
                $sendEmail = true; // Send email for other addresses
            }

            // Save OTP and email in the database
            try {
                date_default_timezone_set('Asia/Calcutta');
                $current_date = date("Y-m-d H:i:s", time());
                $email_verification = new EmailVerificationModel();
                $email_verification->email = $request->input('email');
                $email_verification->otp = $otp;
                $email_verification->created_at = $current_date;
                $email_verification->save();
            } catch (Exception $e) {
                // Handle database insertion error
                $error['message'] = "Error saving OTP and email in the database: " . $e->getMessage();
                $error['success'] = false;
                return response()->json($error, 500);
            }

            if ($sendEmail) {
                // Send OTP via MSG91 Email API using cURL
                try {
                    // Prepare the payload
                    $payload = [
                        "to" => [
                            [
                                "name"  => "User", // Replace "User" with the actual recipient's name if available
                                "email" => "cvmvdev24@gmail.com"
                            ]
                        ],
                        "from" => [
                            "name"  => "Support",
                            "email" => "support@cvmvreddystrust.com"
                        ],
                        "domain"      => "cvmvreddystrust.com",
                        "mail_type_id"=> "1",
                        "template_id" => "CVMV_OTP", // Ensure this matches the template ID in MSG91
                        "variables"   => [
                            "OTP" => "1236"
                            // Add other variables if your template requires them, e.g., "app_name" => "YourAppName"
                        ]
                    ];


                    // Initialize cURL
                    $curl = curl_init();

                    curl_setopt_array($curl, [
                        CURLOPT_URL => 'https://control.msg91.com/api/v5/email/send',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => json_encode($payload),
                        CURLOPT_HTTPHEADER => [
                            'authkey: ' . $authKey,
                            'Content-Type: application/json'
                        ],
                    ]);

                    // Execute the cURL request
                    $response = curl_exec($curl);
                    $err = curl_error($curl);

                    // Close cURL
                    curl_close($curl);

                    if ($err) {
                        // Handle cURL error
                        $error['message'] = "cURL Error: " . $err;
                        $error['success'] = false;
                        return response()->json($error, 500);
                    } else {
                        // Decode the response to check for success
                        $responseData = json_decode($response, true);

                        if (isset($responseData['message']) && strtolower($responseData['message']) === 'success') {
                            $success['message'] = "OTP sent successfully";
                            $success['success'] = true;
                            return response()->json($success);
                        } else {
                            // Handle API error response
                            $error['message'] = "Error sending OTP via email: " . ($responseData['message'] ?? 'Unknown error');
                            $error['success'] = false;
                            return response()->json($error, 500);
                        }
                    }
                } catch (Exception $e) {
                    $error['message'] = "Exception while sending OTP via email: " . $e->getMessage();
                    $error['success'] = false;
                    return response()->json($error, 500);
                }
            } else {
                // Return success message without sending email for the specific email
                $success['message'] = "OTP generated but not sent for specific email";
                $success['success'] = true;
                return response()->json($success);
            }
        }
    }



}
