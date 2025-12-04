<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WishlistController;

Route::get('/', function () {
    $products = DB::select('select * from products');
    $user = session('user');
    $userId = $user['id'] ?? null;
    foreach ($products as $product) {
        $product->images = json_decode($product->images, true);
    }
    $cartitems = [];
    $cartitems = $userId ? app(CartController::class)->getCartItemsNew($userId) : [];
    return view('home',compact('products','cartitems'));
});

Route::get('/all-products', function () {
    $products = DB::select('select * from products');
    $user = session('user');
    $userId = $user['id'] ?? null;
    foreach ($products as $product) {
        $product->is_wishlisted = false;
        if(app(HomeController::class)->checkIsWishlisted($product->_id,$userId)){
            $product->is_wishlisted = true;
        }
        $product->images = json_decode($product->images, true);
    }
    $cartitems = [];
    $cartitems = $userId ? app(CartController::class)->getCartItemsNew($userId) : [];
    return view('all-products',compact('products','cartitems'));
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/register',function(){
    return view('register');
})->name('register');

Route::get('/dashboard',function(){
    $products = DB::select('select * from products');
    $user = session('user');
    $userId = $user['id'] ?? null;
    foreach ($products as $product) {
        $product->is_wishlisted = false;
        if(app(HomeController::class)->checkIsWishlisted($product->_id,$userId)){
            $product->is_wishlisted = true;
        }
        $product->images = json_decode($product->images, true);
    }
    $cartitems = [];
    $cartitems = $userId ? app(CartController::class)->getCartItemsNew($userId) : [];
    //print_r($cartitems); exit;
    return view('dashboard',compact('products','cartitems'));
})->name('dashboard');

Route::get('/checkout',function(){
    $user = session('user');
    $userId = $user['id'];
    $cartitems = $userId ? app(CartController::class)->getCartItemsNew($userId) : [];
    return view('checkout-main',compact('cartitems'));
})->name('checkout');

Route::get('/wishlist',function(){
    $user = session('user');
    $userId = $user['id'];
    $wishlistProducts = app(WishlistController::class)->getWishlist($userId);
    return view('wishlist-main',compact('wishlistProducts'));
});

//Route::get('/login',);

Route::post('login', [AuthController::class, 'login'])->name('login.submit');
Route::post('register',[AuthController::class, 'register'])->name('register.submit');
Route::get('logout',[AuthController::class, 'logout']);
Route::post('/wishlist/store',[WishlistController::class,'storeWishlist'])->name('wishlist.store');
Route::get('/collections/{category?}', [HomeController::class, 'show'])
    ->name('collections.show');

//Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::post('/cart/contents/',[CartController::class,'getCartItems'])->name('cart.show');
Route::post('/cart/add/', [CartController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/increment/', [CartController::class, 'updateQuantity'])->name('cart.increment');
Route::post('/cart/decrement/', [CartController::class, 'updateQuantity'])->name('cart.decrement');
Route::post('/cart/decrement/', [CartController::class, 'updateQuantity'])->name('cart.decrement');
Route::get('/cart/count/', [CartController::class, 'getCartCount'])->name('cart.getcartcount');
Route::post('/cart/remove/', [CartController::class, 'removeFromCart'])->name('cart.remove');
Route::get('/product/{id}',[HomeController::class, 'showProductDetails']) ->name('productDetails.show');
Route::get('/api/products',[HomeController::class, 'apiBulk'])->name('products.apiBulk');
