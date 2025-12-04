<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller {
    public function index(){
        $products = DB::select('select * from products');
        $user = session('user');
        $userId = $user->id;
        echo '<pre>';
        print_r($user);
        echo '</pre>';
        echo $userId;
        exit;
        foreach ($products as $product) {
            $product->is_wishlisted = false;
            $this->checkIsWishlisted($product->_id,$userId);
           
        }
        return view('dashboard',compact($products));
    }

    public function checkIsWishlisted($productId, $userId)
{
    return DB::table('wishlists')
        ->where('user_id', $userId)
        ->where('product_id', $productId)
        ->exists();
}

public function show($category = 'All')
{
    // Raw DB query
    $products = DB::select('SELECT * FROM products');
    $user = session('user');
    $userId = $user->id ?? null;

    foreach ($products as $product) {
        $product->is_wishlisted = false;
        if(app(HomeController::class)->checkIsWishlisted($product->_id,$userId)){
            $product->is_wishlisted = true;
        }
        $product->images = json_decode($product->images, true);
    }

    // Convert to Laravel Collection
    $productsCollection = collect($products);

    // Generate category list
    $categories = $productsCollection
        ->pluck('category')
        ->filter()
        ->unique()
        ->values()
        ->toArray();

    // Add "All" at the top
    array_unshift($categories, 'All');

    // Filter products based on category
    if ($category === 'All') {
        $filteredProducts = $productsCollection;
    } else {
        $filteredProducts = $productsCollection->filter(function ($product) use ($category) {
            return strtolower($product->category) === strtolower($category);
        });
    }

    return view('collection-main', [
        'categories' => $categories,
        'activeCategory' => $category,
        'filteredProducts' => $filteredProducts,
    ]);
}

public function showProductDetails($productId = "")
{
    //echo 'reached here !', exit;
    // Raw DB query
    $user = session('user');
    $userId = $user['id'] ?? null;
    $productDetails = DB::select('SELECT * FROM products WHERE _id = ? LIMIT 1', [$productId]);

    if (empty($productDetails)) {
        abort(404, 'Product not found');
    }

    $product = $productDetails[0];
    $product->images = json_decode($product->images, true);
    $product->is_wishlisted = false;

    if ($this->checkIsWishlisted($product->_id, $userId)) {
        $product->is_wishlisted = true;
    }

    $cartitems = $userId ? app(CartController::class)->getCartItemsNew($userId) : [];

    return view('product-details-main', ['product' => $product , 'cartitems' => $cartitems]);
}

public function apiBulk(Request $request)
{
    $ids = explode(',', $request->ids);

    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $sql = "SELECT * FROM products WHERE id IN ($placeholders)";

    $products = DB::select($sql, $ids);
    foreach ($products as $product) {
        if (!empty($product->images)) {
            $product->images = json_decode($product->images, true);
        }
        else {
            $product->images = [];
        }        
    }

    return $products;

}

}

?>