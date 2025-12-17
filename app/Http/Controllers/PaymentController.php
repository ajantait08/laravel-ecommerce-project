<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function processPayment(Request $request){

        // echo '<pre>';
        // print_r($request->all());
        // echo '</pre>';
        // exit;

    // 1️⃣ Validate incoming data
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

        //echo 'User Info ID: ' . $userInfoId . "\n"; exit;

        //DB::commit();

        return response()->json([
            'success' => true,
            'user_info_id' => $userInfoId,
            'user_id' => $request->user_id ?? null,
            'redirect_url' => route('stripe.session',[$request->user_id, $userInfoId])
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
}
