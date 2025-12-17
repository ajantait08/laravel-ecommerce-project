<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\DB;
use Stripe\Customer;
use Carbon\Carbon;

class StripeController extends Controller
{
    public function session(Request $request , $userId , $userInfoId){

        $order_details = DB::select('select * from order_details where user_info_id = ?',[$userInfoId]);
        $user_info = DB::select('select * from user_info where id = ?',[$userInfoId]);

        Stripe::setApiKey(config('services.stripe.secret'));

        $customer = Customer::create([
            'email' => $user_info[0]->email,
            'name' => $user_info[0]->first_name . ' ' . $user_info[0]->last_name,
            'address' => [
                'line1' => $user_info[0]->address,
                'city' => $user_info[0]->city,
                'state' => $user_info[0]->state,
                'postal_code' => $user_info[0]->pincode,
                'country' => $user_info[0]->country,
            ],
            'metadata' => [
                'user_id' => $userId,
                'user_info_id' => $userInfoId
            ]
        ]);


        $lineItems = [];

        foreach ($order_details as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $item->name,
                        'images' => [$item->image], // MUST be array
                    ],
                    'unit_amount' => (int) round($item->price * 100), // cents
                ],
                'quantity' => $item->quantity,
            ];
        }

        
        $session = Session::create([
            'payment_method_types' => ['card'],
            'customer' => $customer->id,
            'customer_update' => [
            'name' => 'auto',
            'address' => 'auto',
            'shipping' => 'auto',
            ],
            'metadata' => [
                'user_id' => $userId,
                'user_info_id' => $userInfoId
            ],
            'billing_address_collection' => 'required',
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('stripe.success',[$userId,$userInfoId]).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('stripe.cancel',[$userId,$userInfoId]).'?session_id={CHECKOUT_SESSION_ID}'
            ]);

        return redirect($session->url);
    }

    public function success(Request $request , $userId , $userInfoId){    
        
        Stripe::setApiKey(config('services.stripe.secret'));
        $session_id = $request->get('session_id');
        if(!$session_id){
            return redirect()->route('checkout')->with('error','Session ID is missing');
        }

        $session = Session::retrieve($session_id);
        $payment_intent = $session->payment_intent;

        $payment = PaymentIntent::retrieve($payment_intent);

        $payment_date_time = Carbon::createFromTimestamp($payment->created)->format('d-m-Y H:i:s');
        $paymentMethod = $payment->payment_method_types[0] ?? null;
        //echo $payment_date_time; exit;

        if($payment->status == 'succeeded'){
            // Payment was successful
            // Update the order status in the database for the respective tables.
            DB::update('update user_info set payment_intent_id = ? , payment_status = ? , payment_date_time = ? , payment_mode = ? where id = ?',[$payment->id , 'Paid' , $payment_date_time ,$paymentMethod, $userInfoId]);
            
            $isUpdated = DB::select('select * from user_info where payment_status = ? and id = ?',['Paid',$userInfoId]);
            if(empty($isUpdated)){
                return redirect()->route('checkout')->with('error','Failed to update payment status.');
            }
            else{
            // Fetch User Details
            $userInfoDetails = DB::select('select * from user_info where id = ? and user_id = ?',[$userInfoId,$userId]);
            // Fetch Order Details
            $orderDetails = DB::select('select * from order_details where user_info_id = ?',[$userInfoId]);
            // Delete the cart items for the user.
            $deletedRows = DB::delete('delete from cart_items where user_id = ?',[$userId]);
            
            return view('payment-success-main',compact('userInfoDetails','orderDetails'));           
            }
            //return redirect()->route('payment.success')->with('success','Payment successful!');
        }
        else {
            return redirect()->route('payment.error')->with('error','Payment Declined!');
        }
    }

    public function cancel(Request $request , $userId , $userInfoId){

        return redirect()->route('checkout')->with('error','Payment Cancelled!');
    }
}
