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

    public function createPaymentIntent(Request $request)
    {
        $data = $request->validate([
            'payment_gateway' => 'required|string',
    
            'billing.email' => 'required|email',
            'billing.phone' => 'required|string|max:20',
            'billing.first_name' => 'required|string',
            'billing.last_name' => 'required|string',
            'billing.street' => 'required|string',
            'billing.city' => 'required|string',
            'billing.state' => 'required|string',
            'billing.country' => 'required|string',
            'billing.pincode' => 'required|string',
    
            //Cart Items Details
            'cart_items' => 'required|array|min:1',
            'cart_items.*.product_id' => 'required',
            'cart_items.*.name' => 'required',
            'cart_items.*.quantity' => 'required|integer|min:1',
            'cart_items.*.price' => 'required|numeric',
            'cart_items.*.image' => 'required',
            'pricing.total' => 'required|numeric|min:1',
        ]);
    
        // echo 'reached here !';
        // echo '<pre>';
        //     print_r($data);
        //     echo '</pre>';
        //     exit;
    
    
        //DB::beginTransaction();
    
        try {
            //Code to insert into Users-info
            //  echo '<pre>';
            // print_r($data);
            // echo '</pre>';
            // echo $data['billing']['email'];
            //exit;
            $userInfoId = DB::table('user_info')->insertGetId(
                [
                    'user_id' => $request->user_id,
                    'user_email' => $data['billing']['email'],
                    'email' => $data['billing']['email'],
                    'phone' => $data['billing']['phone'],
                    'first_name' => $data['billing']['first_name'],
                    'last_name' => $data['billing']['last_name'],
                    'address' => $data['billing']['street'],
                    'city' => $data['billing']['city'],
                    'state' => $data['billing']['state'],
                    'country' => $data['billing']['country'],
                    'pincode' => $data['billing']['pincode'],
                    'total_amount' => $data['pricing']['total'],
                    'shipping_cost' => $data['pricing']['shipping'] ?? 0,
                    'payment_intent_id' => $request->payment_intent_id ?? null,
                    'payment_gateway' => $data['payment_gateway'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            session(['user_info_id' => $userInfoId]);
    
            $orderDetails = [];
                foreach ($data['cart_items'] as $item) {
                    $orderDetails[] = [
                        'user_info_id' => $userInfoId,
                        'product_id' => $item['product_id'],
                        'name' => $item['name'],
                        'description' => $item['description'] ?? '',
                        'price' => $item['price'],
                        'quantity' => $item['quantity'],
                        'image' => $item['image'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
    
        DB::table('order_details')->insert($orderDetails);
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
                'user_id' => $request->user_id,
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

        // $intent = PaymentIntent::create([
        //     'amount' => $request->amount, // already in paise
        //     'currency' => 'inr',
        //     'automatic_payment_methods' => [
        //         'enabled' => true,
        //     ],
        //     'description' => 'Order payment for checkout',
        //     'metadata' => [
        //         'email' => $request->billing['email'] ?? '',
        //     ],
        // ]);

        $intent = PaymentIntent::create([
            'amount' => (int) round($data['pricing']['total'] * 100),
            'currency' => 'usd',
            'payment_method_types' => ['card'],
            'customer' => $customer->id,
        
            
            'shipping' => [
                'name' => $user_info[0]->first_name . ' ' . $user_info[0]->last_name,
                'address' => [
                    'line1' => $user_info[0]->address,
                    'city' => $user_info[0]->city,
                    'state' => $user_info[0]->state,
                    'postal_code' => $user_info[0]->pincode,
                    'country' => $user_info[0]->country, // usually 'IN'
                ],
            ],
        
            'description' => 'Order payment for checkout',
        
            'metadata' => [
                'user_id' => $request->user_id,
                'user_info_id' => $userInfoId,
                'email' => $user_info[0]->email,
            ],
        
            'receipt_email' => $user_info[0]->email,
        ]);
        

        return response()->json([
            'clientSecret' => $intent->client_secret,
        ]);
    }
    catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

public function stripe_payment_success(Request $request){

    $user = session('user');
    $userId = $user['id'] ?? null;
    if(!$userId){
        return redirect()->route('login')->with('error','Please login to continue.');
    }
    $paymentIntentId = $request->query('payment_intent');
    $paymentIntentClientSecret = $request->query('payment_intent_client_secret');
    $redirectStatus = $request->query('redirect_status');

     // Set Stripe key
     Stripe::setApiKey(config('services.stripe.secret'));

     // Retrieve PaymentIntent from Stripe
     $paymentIntentDetails = PaymentIntent::retrieve($paymentIntentId);
     $userInfoId = $paymentIntentDetails->metadata->user_info_id;
     $userId = $paymentIntentDetails->metadata->user_id;

     $payment_date_time = Carbon::createFromTimestamp($paymentIntentDetails->created)->format('d-m-Y H:i:s');
     $paymentMethod = $paymentIntentDetails->payment_method_types[0] ?? null;
     //exit;
     if($redirectStatus == 'succeeded'){
        DB::update('update user_info set payment_intent_id = ? , payment_status = ? , payment_date_time = ? , payment_mode = ? where id = ?',[$paymentIntentDetails->id , 'Paid' , $payment_date_time ,$paymentMethod, $userInfoId]);
            
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
    }
    else {
        return redirect()->route('payment.error')->with('error','Payment Declined!');
    }
}

}
