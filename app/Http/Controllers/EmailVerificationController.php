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
            return response()->json([
                'success' => false,
                'message' => $validator->messages()
            ], 422);
        }

        $email = $request->input('email');
        $specificEmail = "bpmdev24@gmail.com"; // The specific email to not send OTP

        // Determine whether to send OTP
        if ($email === $specificEmail) {
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
            $email_verification->email = $email;
            $email_verification->otp = $otp;
            $email_verification->created_at = $current_date;
            $email_verification->save();
        } catch (\Exception $e) {
            // Handle database insertion error
            return response()->json([
                'success' => false,
                'message' => "Error saving OTP and email in the database: " . $e->getMessage()
            ], 500);
        }

        if ($sendEmail) {
            // Send OTP via MSG91 Email API using Laravel's HTTP client
            try {
                // Prepare the payload with multiple recipients
                $payload = [
                    "recipients" => [
                        [
                            "to" => [
                                [
                                    // "name" => "Recipient2 name",
                                    "email" => $email
                                ]
                            ],
                            "variables" => [
                                "OTP" => (string)$otp
                            ]
                        ]
                    ],
                    "from" => [
                        "name" => "Support_CVMV",
                        "email" => "support@cvmvreddystrust.com"
                    ],
                    "domain" => "cvmvreddystrust.com",
                    "template_id" => "cvmv_otp_2"
                ];

                // Make the POST request using Laravel's HTTP client
                $response = Http::withHeaders([
                    'authkey' => $authKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post('https://control.msg91.com/api/v5/email/send', $payload);

                // Log the response for debugging
                Log::info('MSG91 API Response:', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'json' => $response->json()
                ]);

                // Decode the response data
                $responseData = $response->json();

                // Check 'status' and 'hasError' to determine success
                if (
                    isset($responseData['status']) &&
                    strtolower($responseData['status']) === 'success' &&
                    isset($responseData['hasError']) &&
                    $responseData['hasError'] === false
                ) {
                    return response()->json([
                        'success' => true,
                        'message' => "OTP sent successfully",
                        'unique_id' => $responseData['data']['unique_id'] ?? null, // Include unique_id if available
                    ]);
                } else {
                    // Handle API error response
                    return response()->json([
                        'success' => false,
                        'message' => "Error sending OTP via email: " . ($responseData['message'] ?? 'Unknown error'),
                        'details' => $responseData // Include full response for debugging
                    ], 500);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => "Exception while sending OTP via email: " . $e->getMessage()
                ], 500);
            }
        } else {
            // Return success message without sending email for the specific email
            return response()->json([
                'success' => true,
                'message' => "OTP generated but not sent for the specific email"
            ]);
        }
    }
}
