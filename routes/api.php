<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\OrderDetailsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/storeWishlist',[WishlistController::class,'storeWishlist']);
Route::get('/wishlist/{user_id}', [WishlistController::class, 'getWishlist']);
Route::get('/cart/{user_id}', [CartController::class, 'getCartItems']);
Route::post('/cart/add', [CartController::class, 'addToCart']);
Route::post('/cart/remove', [CartController::class, 'removeFromCart']);
Route::post('/cart/update', [CartController::class, 'updateQuantity']);
Route::get('/getAllCoupons',[CouponController::class,'getAllCoupons']);
Route::post('/apply-coupon',[CouponController::class,'applyCoupon']);
Route::post('/remove-coupon',[CouponController::class,'removeCoupon']);
Route::get('/temporary-coupon/{user_id}',[CouponController::class,'getTempAppliedCoupon']);
Route::post('/save-order-details',[OrderDetailsController::class,'storeOrder']);
Route::post('/update-order-details',[OrderDetailsController::class,'updateOrder']);

