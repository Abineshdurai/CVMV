<?php

namespace App\Http\Controllers;

use App\Models\EmailVerificationModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;


class EmailVerificationController extends Controller
{

public function sentEmailOTP(Request $request)
{
    // Validate request data
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
    ]);

    if ($validator->fails()) {
        // Return validation error messages if validation fails
        return response()->json($validator->messages(), 200);
    } else {


            // Generate a random 4-digit OTP for other emails
            $otp = rand(1000, 9999);
            $sendEmail = true; // Send email for other addresses


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
            // Send OTP via email
            try {
                $email = new PHPMailer(true);
                $email->isSMTP();
                $email->Host = 'tabsquareinfotech.com';
                $email->SMTPAuth = true;
                $email->Username = 'simson@tabsquareinfotech.com';
                $email->Password = 'Simson@tabsquareinfotech'; // Update with the correct password
                $email->SMTPSecure = 'ssl';
                $email->Port = 465;
                $email->setFrom('simson@tabsquareinfotech.com', 'MyFood MyPrice');
                $email->addAddress($request->email);
                  $email->isHTML(true);
                $email->Subject = 'MyFood MyPrice One-Time Password (OTP)';
                // $email->Body = 'MyFood MyPrice OTP for BiryaniPalayam is  ' . $otp . ' kindly use this for login into your BiryaniPalayam App';
                $email->Body = 'MyFood MyPrice OTP for BiryaniPalayam is <strong><span style="color:blue">' . $otp . '</span></strong> kindly use this for login into your MyFood MyPrice App';

                $email->send();

                $success['message'] = "OTP sent successfully";
                $success['success'] = true;
                return response()->json($success);
            } catch (Exception $e) {
                $error['message'] = "Error sending OTP via email: " . $e->getMessage();
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
