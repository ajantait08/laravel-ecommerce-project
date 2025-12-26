<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WishlistController;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

Route::get('/', function () {
    //$products = DB::select('select * from products');
    $products = DB::table('products')
    ->leftJoin('reviews_rating', 'products._id', '=', 'reviews_rating.product_id')
    ->select(
        'products._id',
        'products.name',
        'products.price',
        'products.description',
        'products.images',
        DB::raw('ROUND(AVG(reviews_rating.rating), 1) as avg_rating'),
        DB::raw('COUNT(reviews_rating.id) as review_count')
    )
    ->groupBy(
        'products._id',
        'products.name',
        'products.price',
        'products.description',
        'products.images'
    )
    ->get();

    $user = session('user');
    $userId = $user['id'] ?? null;
    foreach ($products as $product) {
        $product->images = json_decode($product->images, true);
    }
    //$guestId = $request->cart_id;
    $guestId = request()->cookie('guest_cart_id');
    //echo $guestId;
    $cartitems = [];
    $cartitems = $userId ? app(CartController::class)->getCartItemsNew($userId) : app(CartController::class)->getGuestCartItemsNew($guestId);
    return view('home',compact('products','cartitems'));
})->name('home');

Route::get('/all-products', function (Request $request) {

    // echo '<pre>';
    // print_r($request->all());
    // echo '</pre>';
    // exit;
    
    //$products = DB::select('select * from products');
    // $query = DB::select("select * from products");
    // $conditions = [];
    // $parameters = [];
    // // 1. PRICE
    // if ($request->filled('min_price')) {
    //     $conditions[] = "price >= :min_price";
    //     $parameters['min_price'] = (int) $request->min_price;
    // }
    // if ($request->filled('max_price')) {
    //     $conditions[] = "price <= :max_price";
    //     $parameters['max_price'] = (int) $request->max_price;
    // }
    // // 2. RATING
    // if ($request->filled('rating')) {
    //     $conditions[] = "rating >= :rating";
    //     $parameters['rating'] = (int) $request->rating;
    // }
    // // 3. CATEGORY
    // if ($request->filled('category')) {
    //     $placeholders = implode(',', array_fill(0, count($request->category), '?'));
    //     $conditions[] = "category_id IN ($placeholders)";
    //     $parameters = array_merge($parameters, $request->category);
    // }
    // // 4. BRAND
    // if ($request->filled('brand')) {
    //     $placeholders = implode(',', array_fill(0, count($request->brand), '?'));
    //     $conditions[] = "brand IN ($placeholders)";
    //     $parameters = array_merge($parameters, $request->brand);
    // }

    // // Combine conditions
    // if (!empty($conditions)) {
    //     $query .= " WHERE " . implode(' AND ', $conditions);
    //     //$query = "SELECT * FROM products WHERE " . implode(' AND ', $conditions);
    // }

    // //echo $query; exit;
    // // 5. SORT
    // switch ($request->sort) {
    //     case 'price_low_high':
    //         $query .= " ORDER BY price ASC";
    //         break;
    //     case 'price_high_low':
    //         $query .= " ORDER BY price DESC";
    //         break;
    //     case 'newest':
    //         $query .= " ORDER BY created_at DESC";
    //         break;
    //     case 'discount':
    //         $query .= " ORDER BY discount_percentage DESC";
    //         break;
    //     default:
    //         $query .= " ORDER BY created_at DESC";
    //         break;
    // }
    // $products = DB::select($query, $parameters);

    // // Paginate results manually (20 per page)
    // // Convert array → collection
    // $user = session('user');
    // $userId = $user['id'] ?? null;
    // foreach ($products as $product) {
    //     $product->is_wishlisted = false;
    //     if(app(HomeController::class)->checkIsWishlisted($product->_id,$userId)){
    //         $product->is_wishlisted = true;
    //     }
    //     $product->images = json_decode($product->images, true);
    // }
    // // Convert array → collection
    // $collection = collect($products);

    // // Pagination settings
    // $perPage = 4; // change as needed
    // $currentPage = LengthAwarePaginator::resolveCurrentPage();

    // // Slice data for current page
    // $currentPageItems = $collection->slice(
    //     ($currentPage - 1) * $perPage,
    //     $perPage
    // )->values();

    // // Create paginator
    // $paginator = new LengthAwarePaginator(
    //     $currentPageItems,
    //     $collection->count(),
    //     $perPage,
    //     $currentPage,
    //     [
    //         'path' => request()->url(),
    //         'query' => request()->query(),
    //     ]
    // );


$max_price = $request['max_price'] ?? 4000;
$min_price = $request['min_price'] ?? 0;


$query = "SELECT * FROM products";
$conditions = [];
$parameters = [];

/**
 * 1. PRICE
 */
if ($request->filled('min_price')) {
    $conditions[] = "cast(price AS DECIMAL(10,2)) >= ?";
    $parameters[] = (int) $request->min_price;
}

if ($request->filled('max_price')) {
    $conditions[] = "cast(price AS DECIMAL(10,2)) <= ?";
    $parameters[] = (int) $request->max_price;
}

/**
 * 2. RATING
 */
if ($request->filled('rating')) {
    $conditions[] = "rating >= ?";
    $parameters[] = (int) $request->rating;
}

/**
 * 3. CATEGORY
 */
if ($request->filled('category') && is_array($request->category)) {
    $placeholders = implode(',', array_fill(0, count($request->category), '?'));
    $conditions[] = "category_id IN ($placeholders)";
    $parameters = array_merge($parameters, $request->category);
}

/**
 * 4. BRAND
 */
if ($request->filled('brand') && is_array($request->brand)) {
    $placeholders = implode(',', array_fill(0, count($request->brand), '?'));
    $conditions[] = "brand IN ($placeholders)";
    $parameters = array_merge($parameters, $request->brand);
}

/**
 * Combine WHERE conditions
 */
if (!empty($conditions)) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}

/**
 * 5. SORT
 */
switch ($request->sort) {
    case 'price_low_high':
        $query .= " ORDER BY cast(price as decimal(10,2)) ASC";
        break;

    case 'price_high_low':
        $query .= " ORDER BY cast(price as decimal(10,2)) DESC";
        break;

    case 'newest':
        $query .= " ORDER BY created_at DESC";
        break;

    case 'discount':
        $query .= " ORDER BY discount_percentage DESC";
        break;

    default:
        $query .= " ORDER BY created_at DESC";
        break;
}

/**
 * Execute query
 */

$products = DB::select($query, $parameters);


/**
 * Wishlist + Images
 */
$user = session('user');
$userId = $user['id'] ?? null;

foreach ($products as $product) {
    $product->is_wishlisted = false;

    if ($userId && app(HomeController::class)->checkIsWishlisted($product->_id, $userId)) {
        $product->is_wishlisted = true;
    }

    $product->images = json_decode($product->images, true);
}

/**
 * Manual Pagination
 */
$collection = collect($products);
$perPage = 4;
$currentPage = LengthAwarePaginator::resolveCurrentPage();

$currentPageItems = $collection
    ->slice(($currentPage - 1) * $perPage, $perPage)
    ->values();

$paginator = new LengthAwarePaginator(
    $currentPageItems,
    $collection->count(),
    $perPage,
    $currentPage,
    [
        'path' => request()->url(),
        'query' => request()->query(),
    ]
);
    $guestId = request()->cookie('guest_cart_id');
    $cartitems = [];
    $cartitems = $userId ? app(CartController::class)->getCartItemsNew($userId) : app(CartController::class)->getGuestCartItemsNew($guestId);
    //$category = DB::select('select category from products');
    $category = DB::table('products')
    ->distinct()
    ->pluck('category'); // returns array of strings
    $brand = DB::table('products')
    ->distinct()
    ->pluck('brand');
    return view('all-products',compact('products','cartitems','paginator','max_price','min_price','category','brand'));
})->name('all.products');

Route::get('all-featured-products',function (){
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
    $guestId = request()->cookie('guest_cart_id');
    $cartitems = [];
    $cartitems = $userId ? app(CartController::class)->getCartItemsNew($userId) : app(CartController::class)->getGuestCartItemsNew($guestId);
    return view('all-featured-products-main',compact('products','cartitems'));
});

Route::get('/login', function () {
    return view('login');
})->name('login');



Route::get('/register',function(){
    $from = urldecode($_GET['from'] ?? '');
    return view('register',['from' => $from]);
})->name('register');

Route::get('/dashboard',function(){
    //$products = DB::select('select * from products');
    $products = DB::table('products')
    ->leftJoin('reviews_rating', 'products._id', '=', 'reviews_rating.product_id')
    ->select(
        'products._id',
        'products.name',
        'products.price',
        'products.description',
        'products.images',
        DB::raw('ROUND(AVG(reviews_rating.rating), 1) as avg_rating'),
        DB::raw('COUNT(reviews_rating.id) as review_count')
    )
    ->groupBy(
        'products._id',
        'products.name',
        'products.price',
        'products.description',
        'products.images'
    )
    ->get();

    $user = session('user');
    $userId = $user['id'] ?? null;
    if($userId == null){
        return redirect()->route('home');
    }
    foreach ($products as $product) {
        $product->is_wishlisted = false;
        if(app(HomeController::class)->checkIsWishlisted($product->_id,$userId)){
            $product->is_wishlisted = true;
        }
        $product->images = json_decode($product->images, true);
    }
    $cartitems = [];
    $guestId = request()->cookie('guest_cart_id');
    $guestRecentlyViewedId = request()->cookie('guest_recently_viewed_id');
    // The Below CartItems Logic is edited based on LoggedIn User with Guest User
    if(app(CartController::class)->mergeGuestCartToUserCart($userId,$guestId)){
        $cartitems = $userId ? app(CartController::class)->getCartItemsNew($userId) : [];
    }
    if(app(HomeController::class)->mergeGuestRecentlyViewedToUser($userId,$guestRecentlyViewedId)){
        $recentlyViewedProducts = $userId ? app(HomeController::class)->getRecentlyViewedProducts($userId) : [];
    }
    //print_r($cartitems); exit;
    return view('dashboard',compact('products','cartitems','recentlyViewedProducts'));
})->name('dashboard');

Route::get('/checkout',function(){
    $user = session('user');
    $userId = $user['id'] ?? null;
    $guestId = request()->cookie('guest_cart_id');
    $cartitems = [];
    if($userId){
    if(app(CartController::class)->mergeGuestCartToUserCart($userId,$guestId)){
        $cartitems = $userId ? app(CartController::class)->getCartItemsNew($userId) : [];
    }}
    else{
        $cartitems = $userId ? app(CartController::class)->getCartItemsNew($userId) : app(CartController::class)->getGuestCartItemsNew($guestId);
    }
    return view('checkout-main',compact('cartitems'));
})->name('checkout');

Route::get('/wishlist',function(){
    $user = session('user');
    $userId = $user['id'];
    $wishlistProducts = app(WishlistController::class)->getWishlist($userId);
    return view('wishlist-main',compact('wishlistProducts'));
});

// Sorting , Filtering , Searching and Pagination Routes
//Route::get('');

// Sorting , Filtering , Searching and Pagination Routes

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
Route::post('/cart/count/', [CartController::class, 'getCartCount'])->name('cart.getcartcount');
Route::post('/cart/remove/', [CartController::class, 'removeFromCart'])->name('cart.remove');
Route::get('/product/{id}',[HomeController::class, 'showProductDetails']) ->name('productDetails.show');
Route::get('/api/products',[HomeController::class, 'apiBulk'])->name('products.apiBulk');
Route::get('/delete/cookie',[CartController::class,'deleteCookie'])->name('cookie.delete');
Route::post('/checkout-login',[AuthController::class,'checkoutLogin']);


// Creating Stripe Payment Gateway Routes
//Route::get('stripe/checkout/{}/{}',[StripeController::class,'checkout'])->name('stripe.checkout');
Route::post('stripe/process-payment',[PaymentController::class,'processPayment'])->name('stripe_payment.process');
Route::get('stripe/checkout-session/{userId}/{logId}',[StripeController::class,'session'])->name('stripe.session');
Route::get('stripe/checkout-success/{userId}/{logId}',[StripeController::class,'success'])->name('stripe.success');
Route::get('stripe/checkout-cancel/{userId}/{logId}',[StripeController::class,'cancel'])->name('stripe.cancel');
Route::get('payment-success',[StripeController::class,'stripe_payment_success'])->name('payment.success');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/filter', [ProductController::class, 'filter'])
    ->name('products.filter');
Route::get('/products', function (Request $request) {
    return redirect()->to(
        '/all-products' . ($request->getQueryString() ? '?' . $request->getQueryString() : '')
    );
});

Route::post('/products/store-reviews-ratings',[ProductController::class,'store_reviews_ratings'])->name('reviews.store');



