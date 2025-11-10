<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ContactUs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\contactusmail;

class ContactUsController extends Controller
{
    public function store(Request $request)
    {
        //print_r($request->all()); exit;
        // Validation rules
        $validator = Validator::make($request->all(), [
            'formData.name' => 'required|string|max:100',
            'formData.phone_no' => 'nullable|regex:/^[0-9]{10,15}$/',
            'formData.email' => 'required|email',
            'formData.message' => 'required|string|min:5|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Save contact message
        $contact = DB::insert('INSERT INTO contact_us (user_id , user_email , name , phone_no , email , message , created_at , updated_at) VALUES (? , ? , ?, ?, ?, ?, NOW(), NOW())',[
            $request->input('user_id'),
            $request->input('user_email'),
            $request->input('formData.name'),
            $request->input('formData.phone_no'),
            $request->input('formData.email'),
            $request->input('formData.message')
        ]);

        $contact_us_id = DB::getPdo()->lastInsertId();

        //print_r($contact); exit;

        // Send confirmation email
        if($contact_us_id){

            try {
                $message = $request->input('formData.message');
                $toEmail = $request->input('formData.email');
                $message = "Hello " . $request->input('formData.name') . ",\n\n $message.\n\nBest regards,\nQuickcart Team";
                $subject = "Thank you for contacting Quickcart";
    
                //$response = Mail::to($toEmail)->send(new contactusmail($message, $subject));

                Mail::to($toEmail)->send(new contactusmail($message, $subject));
        
                // Get last inserted contact_us ID
                //$contactUsId = DB::getPdo()->lastInsertId();
        
                // Store email details
                DB::table('sent_emails')->insert([
                    'contact_us_id' => $contact_us_id,
                    'recipient_email' => $toEmail,
                    'subject' => $subject,
                    'message' => $message,
                    'status' => 'sent',
                    'sent_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                DB::table('sent_emails')->insert([
                    'contact_us_id' => $contact_us_id,
                    'recipient_email' => $toEmail,
                    'subject' => $subject,
                    'message' => $message,
                    'status' => 'failed',
                    'sent_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
           
            //print_r($response); exit;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Your message has been received successfully!',
            'data' => $contact_us_id,
        ], 200);
    }
}
