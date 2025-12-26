<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;


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
    
    if($userId != ''){
    foreach ($products as $product) {
        $product->is_wishlisted = false;
        if(app(HomeController::class)->checkIsWishlisted($product->_id,$userId)){
            $product->is_wishlisted = true;
        }
        $product->images = json_decode($product->images, true);
    }
    }
    else {
        foreach ($products as $product) {
            $product->images = json_decode($product->images, true);
        }  
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

    $guestId = request()->cookie('guest_cart_id');
    $cartitems = $userId ? app(CartController::class)->getCartItemsNew($userId) : app(CartController::class)->getGuestCartItemsNew($guestId);
    
    return view('collection-main', [
        'categories' => $categories,
        'activeCategory' => $category,
        'filteredProducts' => $filteredProducts,
        'cartitems' => $cartitems
    ]);
}

public function mergeGuestRecentlyViewedToUser($userId,$guestRecentlyViewedId)
{
    // Fetch guest recently viewed items
    $guestItems = DB::table('recently_viewed')
        ->where('guest_id', $guestRecentlyViewedId)
        ->where('user_id', null)
        ->get();

    foreach ($guestItems as $item) {       
            // Update guest_id to user_id
            DB::table('recently_viewed')
                ->where('guest_id', $guestRecentlyViewedId)
                ->update([
                    'user_id' => $userId
                ]);
    }
    return true;
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

    $guestId = request()->cookie('guest_cart_id');
    $guestRecentlyViewedId = request()->cookie('guest_recently_viewed_id');

    // Create guest id if missing (optional)
    if (!$guestRecentlyViewedId) {
        $guestRecentlyViewedId = encrypt(Str::uuid());
            cookie()->queue(
                cookie(
                    'guest_recently_viewed_id',
                    $guestRecentlyViewedId,
                    525600, // 1 year
                    '/',
                    null,
                    false,
                    false  // HttpOnly = false → JS can read
                )
            );

        $guestRecentlyViewedId = urldecode($guestRecentlyViewedId);
    }

    

    // If user is logged in, merge guest recently viewed items
    if ($userId && $guestRecentlyViewedId) {
        //echo 'reached_here_1'; exit;
        //$this->mergeGuestRecentlyViewedToUser($guestRecentlyViewedId, $userId);
        $existing_user = DB::select("select id from recently_viewed where user_id = ? and guest_id = ? and product_id = ? limit 1",[$userId, $guestRecentlyViewedId , $productId]);
        if($existing_user){
            //echo 'block 1'; exit;
            // Update timestamp
            DB::update("
                UPDATE recently_viewed 
                SET viewed_at = NOW()
                WHERE id = ?
            ", [$existing_user[0]->id]);
        } else {
            //echo 'block 2'; exit;
            // Insert new entry
            DB::insert("
                INSERT INTO recently_viewed (user_id,guest_id,product_id, viewed_at)
                VALUES (?,?,?,NOW())
            ", [$userId,$guestRecentlyViewedId,$productId]);
        }

        $deleteUserExtra = DB::select("select id from recently_viewed where user_id = ? and guest_id = ? ORDER BY viewed_at DESC LIMIT 10,1000",[$userId,$guestRecentlyViewedId]);
        if($deleteUserExtra){
            $deleteUserIds = array_column($deleteUserExtra,'id');
            if(!empty($deleteUserIds)){
                $placeholders = implode(',',array_fill(0,count($deleteUserIds),'?'));
                // Debug for seeing why received error for this line
                //DB::delete("DELETE FROM recently_viewed where id IN $placeholders",$deleteUserIds);
            }
        }

        $recentIds = DB::select("
            SELECT distinct product_id 
            FROM recently_viewed 
            WHERE user_id = ? and guest_id = ?
            ORDER BY viewed_at DESC
            LIMIT 15", [$userId, $guestRecentlyViewedId]);

        // Convert to array of only IDs
        $recentIds = array_column($recentIds, 'product_id');

        // Remove current product from the recently viewed list
        $recentIds = array_values(array_filter($recentIds, fn($id) => $id != $productId));

        // Limit to 10
        $recentIds = array_slice($recentIds, 0, 10);
        $recentProducts = [];
        if (!empty($recentIds)) {
            // Convert ids to comma-separated list
            $placeholders = implode(',', array_map(fn($id)=>"'$id'",$recentIds));

            $recentProducts = DB::select("SELECT _id, name , price, images
            FROM products
            WHERE _id IN ($placeholders)
            ORDER BY FIELD(_id,$placeholders)");

            // Decode images JSON
            foreach ($recentProducts as $rp) {
                $rp->images = json_decode($rp->images, true);
            }
        }
    }

    else {
        //echo 'reached_here_2'; exit;
    // Check if product already exists for this guest
    $existing = DB::select("
        SELECT id FROM recently_viewed
        WHERE guest_id = ? AND product_id = ?
        LIMIT 1
    ", [$guestRecentlyViewedId, $productId]);

    if ($existing) {
        // Update timestamp
        DB::update("
            UPDATE recently_viewed 
            SET viewed_at = NOW()
            WHERE id = ?
        ", [$existing[0]->id]);
    } else {
        // Insert new entry
        DB::insert("
            INSERT INTO recently_viewed (guest_id, product_id, viewed_at)
            VALUES (?, ?, NOW())
        ", [$guestRecentlyViewedId, $productId]);
    }

    // Keep only the latest 10 & delete the rest
    $deleteExtra = DB::select("
        SELECT id FROM recently_viewed
        WHERE guest_id = ?
        ORDER BY viewed_at DESC
        LIMIT 10, 1000
    ", [$guestRecentlyViewedId]);

    if ($deleteExtra) {
        $deleteIds = array_column($deleteExtra, 'id');

        if (!empty($deleteIds)) {
            $placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
            DB::delete("
                DELETE FROM recently_viewed WHERE id IN ($placeholders)
            ", $deleteIds);
        }
    }

        $recentIds = DB::select("
            SELECT distinct product_id 
            FROM recently_viewed 
            WHERE guest_id = ?
            ORDER BY viewed_at DESC
            LIMIT 15
        ", [$guestRecentlyViewedId]);

        // Convert to array of only IDs
        $recentIds = array_column($recentIds, 'product_id');

        // Remove current product from the recently viewed list
        $recentIds = array_values(array_filter($recentIds, fn($id) => $id != $productId));

        // Limit to 10
        $recentIds = array_slice($recentIds, 0, 10);

        $recentProducts = [];

        if (!empty($recentIds)) {
            // Convert ids to comma-separated list
            $placeholders = implode(',', array_map(fn($id)=>"'$id'",$recentIds));


            $recentProducts = DB::select("SELECT _id, name , price, images
            FROM products
            WHERE _id IN ($placeholders)
            ORDER BY FIELD(_id,$placeholders)");

            // Decode images JSON
            foreach ($recentProducts as $rp) {
                $rp->images = json_decode($rp->images, true);
            }
        }
    }

        $reviews = collect(DB::select('select * from reviews_rating where product_id = ? order by review_date desc', [$productId]));
        $averageRating = round($reviews->avg('rating'), 1);

        $cartitems = $userId ? app(CartController::class)->getCartItemsNew($userId) : app(CartController::class)->getGuestCartItemsNew($guestId);

        return view('product-details-main', ['product' => $product , 'cartitems' => $cartitems , 'recentProducts' => $recentProducts , 'reviews' => $reviews , 'averageRating' => $averageRating]);
    }

    public function getRecentlyViewedProducts($userId){
    $recentIds = DB::select("
        SELECT distinct product_id 
        FROM recently_viewed 
        WHERE user_id = ?
        ORDER BY viewed_at DESC
        LIMIT 10", [$userId]);
        // Convert to array of only IDs
        $recentIds = array_column($recentIds, 'product_id');
        $recentProducts = [];

    if (!empty($recentIds)) {
        // Convert ids to comma-separated list
        $placeholders = implode(',', array_map(fn($id)=>"'$id'",$recentIds));

        $recentProducts = DB::select("SELECT _id, name , price, images
        FROM products
        WHERE _id IN ($placeholders)
        ORDER BY FIELD(_id,$placeholders)");

        // Decode images JSON
        foreach ($recentProducts as $rp) {
            $rp->images = json_decode($rp->images, true);
        }

    }

    return $recentProducts;

}

public function apiBulk(Request $request)
{
    // Convert CSV string → array
    $ids = explode(',', $request->ids);

    // If empty, return empty array
    if (empty($ids)) {
        return [];
    }

    $recentlyViewedId = $request->cookie('guest_recently_viewed_id');
    if(!$recentlyViewedId){
        $recentlyViewedId = encrypt(Str::uuid());
        cookie()->queue(
            cookie(
                'guest_recently_viewed_id',
                $recentlyViewedId,
                525600, // 1 year
                '/',
                null,
                false,
                false  // HttpOnly = false → JS can read
            )
        );
    }
    $recentlyViewedId = urldecode($recentlyViewedId);

    // DB::listen(function ($query) {
    // dd($query->sql, $query->bindings, $query->time);
    // });

    //foreach ($ids as $productId) {

        // Remove old duplicates
        // DB::table('recently_viewed')
        //     ->where('guest_id', $recentlyViewedId)
        //     ->where('product_id', $productId)
        //     ->delete();

        $latestViewedId = $ids[0];

        // Insert new entry
        DB::table('recently_viewed')->insert([
            'guest_id'   => $recentlyViewedId,
            'product_id' => $latestViewedId,
            'viewed_at'  => now()->format('d-m-Y H:i:s'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    //}

    //OPTIONAL: keep only last 20 views
    // Get ALL items after the latest 20
    $deleteIds = DB::select(
        "SELECT id
         FROM recently_viewed
         WHERE guest_id = ?
         ORDER BY viewed_at DESC
         LIMIT 18446744073709551615 OFFSET 10",
         [$recentlyViewedId]
    );
    
    
    // Convert result objects → array of IDs
    $deleteIdsArray = array_map(function ($item) {
        return $item->id;
    }, $deleteIds);
    
    // Delete only if we have IDs
    if (!empty($deleteIdsArray)) {
        DB::table('recently_viewed')
            ->whereIn('id', $deleteIdsArray)
            ->delete();
    }


    // Prepare placeholders for SQL
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    // Fetch products (unordered)
    $sql = "SELECT * FROM products WHERE id IN ($placeholders)";
    $products = DB::select($sql, $ids);

    // Decode images
    foreach ($products as $product) {
        $product->images = !empty($product->images)
            ? json_decode($product->images, true)
            : [];
    }

    // 🔥 Reorder products based on incoming ID order
    $productMap = [];
    foreach ($products as $p) {
        $productMap[$p->id] = $p;
    }

    $orderedProducts = [];
    foreach ($ids as $id) {
        if (isset($productMap[$id])) {
            $orderedProducts[] = $productMap[$id];
        }
    }

    // Return in correct order
    return $orderedProducts;
}
}

?>