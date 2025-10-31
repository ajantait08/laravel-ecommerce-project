<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CouponController extends Controller
{
    public function getAllCoupons(){
        $coupons = DB::select('select * from coupons');
        return response()->json([
            'status' => true,
            'coupons' => $coupons ?? [] 
        ]);
    }

    public function applyCoupon(Request $request)
    {
        // $request->validate([
        //     'code' => 'required|string',
        //     'subtotal' => 'required|numeric|min:0',
        // ]);

        $coupon = DB::select('select * from coupons where code = ? limit 1', [$request->code]);
        $coupon = $coupon ? (array)$coupon[0] : [];

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid coupon code.',
            ], 404);
        }

        if (!$coupon['is_active']) {
            return response()->json([
                'success' => false,
                'error' => 'This coupon is not active.',
            ], 400);
        }

        if ($coupon['expiry_at'] && Carbon::now()->gt($coupon['expiry_at'])) {
            return response()->json([
                'success' => false,
                'error' => 'This coupon has expired.',
            ], 400);
        }

        if ($coupon['usage_limit'] == 0){
            return response()->json([
                'success' => false,
                'error' => 'This coupon has reached its usage limit.',
            ], 400);
        }

        // check if coupon already used by the user
        $existingUsage = DB::select('SELECT * FROM temporary_coupons WHERE user_id = ? AND coupon_code = ? and coupon_status = ?', [$request->user_id, $coupon['code'] , 1]);
        if ($existingUsage) {
            return response()->json([
                'success' => false,
                'error' => 'You have already used this coupon.',
            ], 400);
        }

        // if($coupon['usage_limit'] > 0){
        //     $usageCount = DB::select('SELECT * FROM temporary_coupons WHERE coupon_code = ? and coupon_status IS NULL', [$coupon['code']]);
        //     $usageCount = $usageCount[0]->count ?? 0;

        //     if ($usageCount >= $coupon['usage_limit']) {
        //         return response()->json([
        //             'success' => false,
        //             'error' => 'This coupon has reached its usage limit.',
        //         ], 400);
        //     }

        // }

        // Calculate discount and final total
        $discountRate = $coupon['value'] / 100; // convert from % to decimal
        $discountAmount = $request->subTotal * $discountRate;
        $finalTotal = $request->subTotal - $discountAmount;
        $user_id = $request->user_id;
        $user_email = $request->user_email;

        $couponsUsed = DB::insert('INSERT INTO temporary_coupons
        (user_id, coupon_code, discount_rate, discount_amount, final_total, user_email)
        VALUES (?, ?, ?, ?, ? , ?)',[$user_id, $coupon['code'], $discountRate, $discountAmount, $finalTotal, $user_email]);
        
        if($couponsUsed) {
        return response()->json([
            'success' => true,
            'code' => $coupon['code'],
            'discount' => round($discountAmount, 2),
            'discount_rate' => $discountRate,
            'final_total' => round($finalTotal, 2),
            'message' => 'Coupon applied successfully!',
        ]);
    }
    }

    public function getTempAppliedCoupon($user_id){
        $tempCoupons = DB::select('select * from temporary_coupons where user_id = ? and coupon_status IS NULL', [$user_id]);
        return response()->json([
            'status' => true,
            'temporary_coupons' => $tempCoupons ?? [],
            'ok' => true
        ]);
    }

    public function removeCoupon(Request $request){

        DB::delete(
            'DELETE FROM temporary_coupons WHERE user_id = ? and coupon_status IS NULL',
            [$request->user_id]
        );
    
        return response()->json([
            'success' => true,
            'message' => 'Coupon removed successfully.',
        ], 200);

    }
}
