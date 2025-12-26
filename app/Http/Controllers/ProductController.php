<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductController extends Controller
{
    public function index(Request $request)
{

    $user = session('user');
    $userId = $user['id'] ?? null;

    $guestId = request()->cookie('guest_cart_id');
    // echo '<pre>';
    // print_r($request->all());
    // echo '</pre>';
    // exit;
    $query = DB::select("select * from products");
    $conditions = [];
    $parameters = [];
    // 1. PRICE
    if ($request->filled('min_price')) {
        $conditions[] = "price >= :min_price";
        $parameters['min_price'] = (int) $request->min_price;
    }
    if ($request->filled('max_price')) {
        $conditions[] = "price <= :max_price";
        $parameters['max_price'] = (int) $request->max_price;
    }
    // 2. RATING
    if ($request->filled('rating')) {
        $conditions[] = "rating >= :rating";
        $parameters['rating'] = (int) $request->rating;
    }
    // 3. CATEGORY
    if ($request->filled('category')) {
        $placeholders = implode(',', array_fill(0, count($request->category), '?'));
        $conditions[] = "category_id IN ($placeholders)";
        $parameters = array_merge($parameters, $request->category);
    }
    // 4. BRAND
    if ($request->filled('brand')) {
        $placeholders = implode(',', array_fill(0, count($request->brand), '?'));
        $conditions[] = "brand IN ($placeholders)";
        $parameters = array_merge($parameters, $request->brand);
    }

    // Combine conditions
    if (!empty($conditions)) {
        //$query .= " WHERE " . implode(' AND ', $conditions);
        $query = "SELECT * FROM products WHERE " . implode(' AND ', $conditions);
    }

    //echo $query; exit;
    // 5. SORT
    switch ($request->sort) {
        case 'price_low_high':
            $query .= " ORDER BY price ASC";
            break;
        case 'price_high_low':
            $query .= " ORDER BY price DESC";
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
    $products = DB::select($query, $parameters);

    foreach ($products as $product) {
        $product->is_wishlisted = false;

        if ($userId && app(HomeController::class)->checkIsWishlisted($product->_id, $userId)) {
            $product->is_wishlisted = true;
        }

        $product->images = json_decode($product->images, true);
    }

    // Paginate results manually (20 per page)
    // Convert array → collection
    $collection = collect($products);

    // Pagination settings
    $perPage = 4; // change as needed
    $currentPage = LengthAwarePaginator::resolveCurrentPage();

    // Slice data for current page
    $currentPageItems = $collection->slice(
        ($currentPage - 1) * $perPage,
        $perPage
    )->values();

    // Create paginator
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

    $max_price = $request['max_price'] ?? 4000;
    $min_price = $request['min_price'] ?? 0;

    $cartitems = [];
    $cartitems = $userId ? app(CartController::class)->getCartItemsNew($userId) : app(CartController::class)->getGuestCartItemsNew($guestId);

    // 🔥 AJAX response
    if ($request->ajax()) {
        //return view('components.product-grid', ['products' => $products,'paginator' => $paginator])->render();
        return view('components.partials.updatedProductsSection', ['products' => $products,'paginator' => $paginator, 'cartitems' => $cartitems , 'max_price' => $max_price , 'min_price' => $min_price])->render();
    }
    return view('components.partials.updatedProductsSection', ['products' => $products,'paginator' => $paginator, 'cartitems' => $cartitems , 'max_price' => $max_price , 'min_price' => $min_price])->render();
    // $query = Product::query();



    // PRICE
    // if ($request->filled('min_price')) {
    //     $query->where('price', '>=', (int) $request->min_price);
    // }

    // if ($request->filled('max_price')) {
    //     $query->where('price', '<=', (int) $request->max_price);
    // }

    // // RATING
    // if ($request->filled('rating')) {
    //     $query->where('rating', '>=', (int) $request->rating);
    // }

    // // CATEGORY
    // if ($request->filled('category')) {
    //     $query->whereIn('category_id', $request->category);
    // }

    // // BRAND
    // if ($request->filled('brand')) {
    //     $query->whereIn('brand', $request->brand);
    // }

    // // SORT
    // match ($request->sort) {
    //     'price_low_high' => $query->orderBy('price', 'asc'),
    //     'price_high_low' => $query->orderBy('price', 'desc'),
    //     'newest' => $query->orderBy('created_at', 'desc'),
    //     'discount' => $query->orderBy('discount_percentage', 'desc'),
    //     default => $query->orderBy('popularity', 'desc'),
    // };

    // $products = $query->paginate(20);

    // // 🔥 AJAX response
    // if ($request->ajax()) {
    //     return view('components.product-grid', compact('products'))->render();
    // }

    // return view('products.all-products', compact('products'));
}

public function filter(Request $request)
{
// echo '<pre>';
// print_r($request->all());
// echo '</pre>';
// exit;
$query = "SELECT * FROM products";
$conditions = [];
$parameters = [];

/**
 * 1. PRICE
 */
if ($request->filled('min_price')) {
    $conditions[] = "cast(price as decimal(10,2)) >= ?";
    $parameters[] = (int) $request->min_price;
}

if ($request->filled('max_price')) {
    $conditions[] = "cast(price as decimal(10,2)) <= ?";
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

    $max_price = $request['max_price'] ?? 4000;
    $min_price = $request['min_price'] ?? 0;
    $guestId = request()->cookie('guest_cart_id');
    $cartitems = [];
    $cartitems = $userId ? app(CartController::class)->getCartItemsNew($userId) : app(CartController::class)->getGuestCartItemsNew($guestId);
    return view('components.partials.updatedProductsSection',compact('products','cartitems','paginator','max_price','min_price'))->render();
}


public function store_reviews_ratings(Request $request){
    
    $product_id =  $request->product_id;
    $rating_value = $request->rating_value;
    $review = $request->review_text;
    $user = session('user');
    $userId = $user['id'] ?? null;
    $data_insert = false;
    $data_update = false;

    $check_review = DB::selectOne("select id from reviews_rating where user_id = ? and product_id = ?",[$userId,$product_id]);
    if($check_review){
    $data_update = DB::update("update reviews_rating set rating = ? , review = ? , review_date = NOW() where user_id = ? and product_id = ?",[$rating_value,$review,$userId,$product_id]);
    }
    else{
    $data_insert = DB::insert("INSERT INTO reviews_rating (user_id, product_id, rating, review, review_date) VALUES (?,?,?,?,NOW())",
    [$userId,$product_id,$rating_value,$review]);
    }

    if($data_insert || $data_update){
        return response()->json(
            ['success' => true,
            'message' => 'Review submitted successfully'
            ]
        );
    }
    else {
        return response()->json([
            'success' => false,
            'message' => 'Failed to submit review'
        ]);
    }


}


}
