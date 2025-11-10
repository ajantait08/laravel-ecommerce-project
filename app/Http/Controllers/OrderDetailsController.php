<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class OrderDetailsController extends Controller
{
    public function storeOrder(Request $request)
    {
        $data = $request->all();
        $applied_coupon = DB::select('select coupon_code from temporary_coupons where user_id = ? and coupon_status IS NULL order by id desc limit 1', [$data['user_id']]);

        if (!empty($applied_coupon)) {
            $data['applied_coupon'] = $applied_coupon[0]->coupon_code;
        } else {
            $data['applied_coupon'] = "";
        }

        DB::beginTransaction();

        try {
            // ✅ Insert into user_info
            //DB::enableQueryLog();
            
            $userInfoId = DB::table('user_info')->insertGetId([
                'user_id' => $data['user_id'] ?? null,
                'user_email' => $data['user_email'] ?? null,
                'first_name' => $data['form']['firstName'] ?? null,
                'last_name' => $data['form']['lastName'] ?? null,
                //'email' => $data['form']['email'] ?? null,
                'email' => $data['user_email'] ?? null,
                'phone' => $data['form']['phone'] ?? null,
                'country' => $data['form']['country'] ?? null,
                'address' => $data['form']['street'] ?? null,
                'apartment' => $data['form']['apartment'] ?? null,
                'city' => $data['form']['city'] ?? null,
                'state' => $data['form']['state'] ?? null,
                'pincode' => $data['form']['pincode'] ?? null,
                'notes' => $data['form']['notes'] ?? 'Storing User Information',
                'coupon_code' => $data['applied_coupon'] ?? null,
                'total_amount' => $data['total_amount'] ?? 0,
                'shipping_cost' => $data['shipping_cost'] ?? 0,
                'payment_intent_id' => $data['payment_intent_id'] ?? null,
                'payment_status' => $data['payment_status'] ?? 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            //dd(DB::getQueryLog());

            // ✅ Insert into order_details (multiple items)
            $orderDetails = [];
            foreach ($data['cart_items'] as $item) {
                $orderDetails[] = [
                    'user_info_id' => $userInfoId,
                    'product_id' => $item['product_id'],
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'image' => $item['image'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('order_details')->insert($orderDetails);

            DB::commit();

            //exit;

            return response()->json([
                'success' => true,
                'message' => 'Order stored successfully',
                'user_info_id' => $userInfoId
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error storing order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateOrder(Request $request)
    {
        $data = $request->all();
        $updated_at = Carbon::now('Asia/Kolkata')->format('d-m-y H:i:s'); //exit;
        $user_info_id = base64_decode($data['user_info_id']);

        try {
            // ✅ Update user_info
            DB::table('user_info')
                ->where('id', $user_info_id)
                ->update([
                    'payment_status' => 'success' ?? 'pending',
                    'payment_date_time' => $updated_at,
                    'payment_intent_id' => $data['payment_intent'] ?? null,
                    'payment_mode' => 'card' ?? null,
                ]);

            // Fetch Order Details and User Info
            $orderDetails = DB::select('select * from order_details where user_info_id = ?', [$user_info_id]);

            $fetchUserDetails = DB::table('user_info')
                ->where('id', $user_info_id)
                ->first();

            $get_user_id_coupon_code = DB::select(
                'SELECT user_id, coupon_code FROM user_info WHERE id = ? LIMIT 1',
                [$user_info_id]
            );

            // If coupon applied, update its usage status
            if (!empty($get_user_id_coupon_code[0]->coupon_code)) {
                DB::table('temporary_coupons')
                    ->where('coupon_code', $get_user_id_coupon_code[0]->coupon_code)
                    ->update(['coupon_status' => 1]);
            }

            // Update the usage coupon count in the coupons table

            DB::table('coupons')
                    ->where('code', $get_user_id_coupon_code[0]->coupon_code)
                    ->decrement('usage_limit', 1);

            // Delete cart items for the user
            DB::table('cart_items')
                ->where('user_id', $get_user_id_coupon_code[0]->user_id)
                ->delete();

            $discounted_coupon_details = DB::select('select * from temporary_coupons where user_id = ? and coupon_code = ? and coupon_status = 1 order by id desc limit 1', [$get_user_id_coupon_code[0]->user_id,$get_user_id_coupon_code[0]->coupon_code]);
            
                
            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'order_details' => $orderDetails,
                'user_info' => $fetchUserDetails,
                'shipping_cost' => $fetchUserDetails->shipping_cost,
                'discount_amount' => $discounted_coupon_details[0]->discount_amount ?? 0,
                'coupon_code' => $discounted_coupon_details[0]->coupon_code ?? null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeTempCheckoutSession(Request $request){
        $data = $request->all();
        $uuid = Str::uuid();
        try {
            $productDetails = DB::select(
                'SELECT * FROM products WHERE _id = ? LIMIT 1',
                [$request->product_id]
            );
            $images = [];
            if (!empty($productDetails[0]->images)) {
                if (is_string($productDetails[0]->images)) {
                    $decoded = json_decode($productDetails[0]->images, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $images = $decoded;
                    }
                } elseif (is_array($productDetails[0]->images)) {
                    $images = $productDetails[0]->images;
                }
            }
            $singleImage = !empty($images) ? $images[0] : null;

            $existingSessionData = DB::select('select count(*) as totalCount from temp_checkout_sessions');
            if($existingSessionData[0]->totalCount > 0){
                DB::update('UPDATE temp_checkout_sessions SET current_status = 0'); 
            }
            DB::insert('INSERT INTO temp_checkout_sessions (session_id, product_id, user_id, user_email, quantity, name, description, image, price, current_status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())', [
                $uuid,
                $request->product_id,
                $request->user_id,
                $request->user_email,
                1, // for now making the quantity as static but will later change it to dynamic
                $productDetails[0]->name,
                $productDetails[0]->description,
                $singleImage,
                $productDetails[0]->price,
                1
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Temporary checkout session stored successfully',
                'session_id' => $uuid
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error storing temporary checkout session: ' . $e->getMessage()
            ], 500);
        }

    }

    public function makeAllTempSessionsInactive(){
        try {
            DB::update('UPDATE temp_checkout_sessions SET current_status = 0'); 
            return response()->json([
                'success' => true,
                'message' => 'All temporary checkout sessions marked as inactive successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating temporary checkout sessions: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTempSessionData($sessionId)
{
    try {
        // Fetch session record (validate session exists)
        $session = DB::table('temp_checkout_sessions')
            ->where('session_id', $sessionId)
            ->first();

        if (!$session) {
            return response()->json([
                'success' => true,
                'data' => [], // ✅ Return empty array instead of 404
            ], 200);
        }

        // Fetch only active record for this session
        $newItem = DB::select(
            'SELECT * FROM temp_checkout_sessions WHERE session_id = ? AND current_status = 1 LIMIT 1',
            [$sessionId]
        );

        // ✅ Safely handle empty result set
        if (!empty($newItem) && isset($newItem[0])) {
            return response()->json([
                'success' => true,
                'data' => $newItem[0],
            ], 200);
        }

        // ✅ Return empty array when no active record found
        return response()->json([
            'success' => true,
            'data' => [],
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching checkout session',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
