<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\contactusmail;

class EmailController extends Controller
{
    public function sendEmail(){
        $toEmail = "ajanta.ghosh@codeclouds.in";
        $message = "Hello, Welcome to our service, please let us know how we can assist you.";
        $subject = "Welcome to Quickcart";

        $request = Mail::to($toEmail)->send(new contactusmail($message,$subject));
        dd($request);
    }
}
