<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;

class CartController extends Controller
{
    // ✅ Get all cart items for a specific user
    public function getCartItemsNew($user_id)
    {
        //echo 'reached here @#'; exit;
        $guestId = request()->cookie('guest_cart_id');
        if($user_id){
        $items = DB::select('SELECT * FROM cart_items WHERE user_id = ?', [$user_id]);
        }
        else {
        $items = DB::select('SELECT * FROM carts WHERE cart_id = ?', [$guestId]);
        }
        return $items ?? [];
    }

    public function getGuestCartItemsNew($guest_id){
        $items = DB::select('SELECT * FROM carts WHERE cart_id = ?', [$guest_id]);
        return $items ?? [];
    }

    public function getCartItems(Request $request)
    {
        //echo 'reached here @#'; exit;
        $guestId = request()->cookie('guest_cart_id');
        if($request->user_id != ''){
        $items = DB::select('SELECT * FROM cart_items WHERE user_id = ?', [$request->user_id]);
        }
        else {
        $items = DB::select('SELECT * FROM carts WHERE cart_id = ?', [$guestId]);    
        }
        return $items ?? [];
    }

    // public function getCartItems(Request $request)
    // {
    //     //echo 'reached here @#'; exit;
    //     $items = DB::select('SELECT * FROM cart_items WHERE user_id = ?', [$request->user_id]);

    //     return $items ?? [];
    // }

    // ✅ Add item to cart or update existing item quantity

    public function addToCart(Request $request)
{
    $userId  = $request->user_id;
    $cartId  = $request->cart_id;
    $productId = $request->product_id;

    // ---------------------------------------------------------
    // CASE 1: LOGGED-IN USER CART
    // ---------------------------------------------------------
    if (!empty($userId)) {

        // Check existing cart item
        $existing = DB::select(
            'SELECT * FROM cart_items WHERE user_id = ? AND product_id = ? LIMIT 1',
            [$userId, $productId]
        );

        if ($existing) {
            DB::update(
                'UPDATE cart_items SET quantity = quantity + 1, updated_at = ? 
                 WHERE user_id = ? AND product_id = ?',
                [now(), $userId, $productId]
            );

            return response()->json([
                'status' => true,
                'message' => 'Quantity updated'
            ]);
        }

        // Fetch product details
        $p = DB::select('SELECT * FROM products WHERE _id = ? LIMIT 1', [$productId])[0];

        $images = json_decode($p->images, true) ?? [];
        $image = $images[0] ?? null;

        DB::insert(
            'INSERT INTO cart_items (user_id, product_id, name, description, price, quantity, image, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$userId, $productId, $p->name, $p->description, $p->price, 1, $image, now(), now()]
        );

        return response()->json([
            'status' => true,
            'message' => 'Item added'
        ]);
    }

    // ---------------------------------------------------------
    // CASE 2: GUEST USER CART (COOKIE BASED)
    // ---------------------------------------------------------

    // If cookie does NOT exist → generate and store it
    $cartId = Cookie::get('guest_cart_id');
    if (!$cartId) {
        $cartId = encrypt(Str::uuid());

        cookie()->queue(
            cookie(
                'guest_cart_id',
                $cartId,
                525600, // 1 year
                '/',
                null,
                false,
                false  // HttpOnly = false → JS can read
            )
        );
    }

    $cartId = urldecode($cartId);

    // DB::listen(function ($query) {
    // dd($query->sql, $query->bindings, $query->time);
    // });

    // Check if product exists in guest cart
    $existing = DB::select(
        'SELECT * FROM carts WHERE cart_id = ? AND product_id = ? LIMIT 1',
        [$cartId, $productId]
    );

    // echo '<pre>';
    // print_r($existing);
    // echo '</pre>';
    // exit;

    if ($existing) {
        DB::update(
            'UPDATE carts SET quantity = quantity + 1, updated_at = ? 
             WHERE cart_id = ? AND product_id = ?',
            [now(), $cartId, $productId]
        );

        return response()->json([
            'status' => true,
            'message' => 'Quantity updated'
        ]);
    }

    // Get product details
    $p = DB::select('SELECT * FROM products WHERE _id = ? LIMIT 1', [$productId])[0];

    $images = json_decode($p->images, true) ?? [];
    $image = $images[0] ?? null;

    // Insert new guest cart item
    DB::insert(
        'INSERT INTO carts (cart_id, product_id, name, description, price, quantity, image, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [$cartId, $productId, $p->name, $p->description, $p->price, 1, $image, now(), now()]
    );

    return response()->json([
        'status' => true,
        'message' => 'Item added'
    ]);
  }

  public function mergeGuestCartToUserCart($userId , $guestId){
    $guestCartItems = DB::select('SELECT * FROM carts WHERE cart_id = ?', [$guestId]);

    foreach($guestCartItems as $item){
        // Check if item exists in user cart
        $existing = DB::select(
            'SELECT * FROM cart_items WHERE user_id = ? AND product_id = ? LIMIT 1',
            [$userId, $item->product_id]
        );

        if ($existing) {
            // Update quantity in user cart
            DB::update(
                'UPDATE cart_items SET quantity = quantity + ?, updated_at = ? 
                 WHERE user_id = ? AND product_id = ?',
                [$item->quantity, now(), $userId, $item->product_id]
            );
        } else {
            // Insert item into user cart
            DB::insert(
                'INSERT INTO cart_items (user_id, product_id, name, description, price, quantity, image, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $userId,
                    $item->product_id,
                    $item->name,
                    $item->description,
                    $item->price,
                    $item->quantity,
                    $item->image,
                    now(),
                    now()
                ]
            );
        }
    }

    // Clear guest cart
    DB::delete('DELETE FROM carts WHERE cart_id = ?', [$guestId]);

    return true;
  }

    // public function addToCart(Request $request)
    // {
    //     //print_r($request->all()); exit;
    //     //echo 'reached here @#'; exit;
    //     // $request->validate([
    //     //     'user_id' => 'required|string',
    //     //     'product_id' => 'required|string',
    //     //     'name' => 'required|string',
    //     //     'description' => 'nullable|string',
    //     //     'price' => 'required|numeric',
    //     //     'quantity' => 'required|integer|min:1',
    //     //     'image' => 'required|string',
    //     // ]);

    //     // Check if the product already exists in the cart for this user
    //     if($request->user_id != ''){
    //     $existingItem = DB::select(
    //         'SELECT * FROM carts WHERE user_id = ? AND product_id = ? LIMIT 1',
    //         [$request->user_id, $request->product_id]
    //     );

    //     if ($existingItem) {
    //         // ✅ Update quantity if item already exists
    //         DB::update(
    //             'UPDATE cart_items 
    //              SET quantity = quantity + ?, updated_at = ? 
    //              WHERE user_id = ? AND product_id = ?',
    //             [1, now(), $request->user_id, $request->product_id]
    //         );

    //         $updatedItem = DB::select(
    //             'SELECT * FROM cart_items WHERE user_id = ? AND product_id = ? LIMIT 1',
    //             [$request->user_id, $request->product_id]
    //         );

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Cart item quantity updated',
    //             'item' => $updatedItem[0]
    //         ], 200);
    //     } else {
    //         //echo 'reached here !2'; exit;
    //         // Get product Details
    //         //DB::enableQueryLog();
            
    //         $productDetails = DB::select(
    //             'SELECT * FROM products WHERE _id = ? LIMIT 1',
    //             [$request->product_id]
    //         );
    //         // Get the executed queries
    //         //$queries = DB::getQueryLog();

    //         //dd($queries);
    //         //print_r($productDetails[0]->name);exit;

    //         $images = [];
    //         if (!empty($productDetails[0]->images)) {
    //             if (is_string($productDetails[0]->images)) {
    //                 $decoded = json_decode($productDetails[0]->images, true);
    //                 if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
    //                     $images = $decoded;
    //                 }
    //             } elseif (is_array($productDetails[0]->images)) {
    //                 $images = $productDetails[0]->images;
    //             }
    //         }
    //         $singleImage = !empty($images) ? $images[0] : null;

    //         // ✅ Insert new item if not exists
    //         DB::insert(
    //             'INSERT INTO cart_items (user_id, product_id, name, description, price, quantity, image, created_at, updated_at) 
    //              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
    //             [
    //                 $request->user_id,
    //                 $request->product_id,
    //                 $productDetails[0]->name,
    //                 $productDetails[0]->description,
    //                 $productDetails[0]->price,
    //                 1,
    //                 $singleImage,
    //                 now(),
    //                 now()
    //             ]
    //         );

    //         $newItem = DB::select(
    //             'SELECT * FROM cart_items WHERE user_id = ? AND product_id = ? LIMIT 1',
    //             [$request->user_id, $request->product_id]
    //         );

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Item added to cart successfully',
    //             'item' => $newItem[0]
    //         ], 201);
    //     }
    //     } else {
    //         // Handle guest user cart using cookies
    //         $existingItem = DB::select(
    //             'SELECT * FROM carts WHERE cart_id = ? AND product_id = ? LIMIT 1',
    //             [$request->cart_id, $request->product_id]
    //         );
    
    //         if ($existingItem) {
    //             // ✅ Update quantity if item already exists
    //             DB::update(
    //                 'UPDATE carts 
    //                  SET quantity = quantity + ?, updated_at = ? 
    //                  WHERE cart_id = ? AND product_id = ?',
    //                 [1, now(), $request->cart_id, $request->product_id]
    //             );
    
    //             $updatedItem = DB::select(
    //                 'SELECT * FROM carts WHERE cart_id = ? AND product_id = ? LIMIT 1',
    //                 [$request->cart_id, $request->product_id]
    //             );
    
    //             return response()->json([
    //                 'status' => true,
    //                 'message' => 'Cart item quantity updated',
    //                 'item' => $updatedItem[0]
    //             ], 200);
    //         } else {
    //             //echo 'reached here !2'; exit;
    //             // Get product Details
    //             //DB::enableQueryLog();

    //             $guestId = (string) encrypt(Str::uuid());
    //             //cookie()->queue(cookie()->forever('guest_cart_id', $guestId));
    //             cookie()->queue(
    //                 cookie(
    //                     'guest_cart_id',     // cookie name
    //                     $guestId,            // cookie value
    //                     525600,              // lifetime in minutes (1 year)
    //                     '/',                 // path
    //                     null,                // domain
    //                     false,               // secure (true in HTTPS)
    //                     false                // HttpOnly = FALSE (very important)
    //                 )
    //             );
                
    //             $productDetails = DB::select(
    //                 'SELECT * FROM products WHERE _id = ? LIMIT 1',
    //                 [$request->product_id]
    //             );
    //             // Get the executed queries
    //             //$queries = DB::getQueryLog();
    
    //             //dd($queries);
    //             //print_r($productDetails[0]->name);exit;
    
    //             $images = [];
    //             if (!empty($productDetails[0]->images)) {
    //                 if (is_string($productDetails[0]->images)) {
    //                     $decoded = json_decode($productDetails[0]->images, true);
    //                     if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
    //                         $images = $decoded;
    //                     }
    //                 } elseif (is_array($productDetails[0]->images)) {
    //                     $images = $productDetails[0]->images;
    //                 }
    //             }
    //             $singleImage = !empty($images) ? $images[0] : null;

    //             DB::listen(function ($query) {
    //                 dd($query->sql, $query->bindings, $query->time);
    //             });
    
    //             // ✅ Insert new item if not exists
    //             DB::insert(
    //                 'INSERT INTO carts (cart_id, product_id, name, description, price, quantity, image, created_at, updated_at) 
    //                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
    //                 [
    //                     $guestId,
    //                     $request->product_id,
    //                     $productDetails[0]->name,
    //                     $productDetails[0]->description,
    //                     $productDetails[0]->price,
    //                     1,
    //                     $singleImage,
    //                     now(),
    //                     now()
    //                 ]
    //             );
    
    //             $newItem = DB::select(
    //                 'SELECT * FROM carts WHERE cart_id = ? AND product_id = ? LIMIT 1',
    //                 [$guestId, $request->product_id]
    //             );
    
    //             return response()->json([
    //                 'status' => true,
    //                 'message' => 'Item added to cart successfully',
    //                 'item' => $newItem[0]
    //             ], 201);
    //         }            
    //     }

    // }

    // ✅ Remove an item from the cart
    public function removeFromCart(Request $request)
    {
        //print_r($request->all()); exit;
        // $request->validate([
        //     'user_id' => 'required|string',
        //     'product_id' => 'required|string',
        // ]);
        //echo $request->user_id; exit;
        $guestId = request()->cookie('guest_cart_id');
        if($request->user_id != ''){
        DB::delete(
            'DELETE FROM cart_items WHERE user_id = ? AND product_id = ?',
            [$request->user_id, $request->product_id]
        );
      } else {
        DB::delete(
            'DELETE FROM carts WHERE cart_id = ? AND product_id = ?',
            [$guestId, $request->product_id]
        );
      }

        return response()->json([
            'status' => true,
            'message' => 'Item removed from cart'
        ], 200);
    }

    // ✅ Update item quantity manually
    public function updateQuantity(Request $request)
    {
        // $request->validate([
        //     'user_id' => 'required|string',
        //     'product_id' => 'required|string',
        //     'quantity' => 'required|integer|min:1',
        // ]);
        $guestId = request()->cookie('guest_cart_id');
        if($request->user_id != ''){
        DB::update(
            'UPDATE cart_items SET quantity = ?, updated_at = ? WHERE user_id = ? AND product_id = ?',
            [$request->quantity, now(), $request->user_id, $request->product_id]
        );
     } else {
        DB::update(
            'UPDATE carts SET quantity = ?, updated_at = ? WHERE cart_id = ? AND product_id = ?',
            [$request->quantity, now(), $guestId, $request->product_id]
        );
     }

        return response()->json([
            'status' => true,
            'message' => 'Cart item quantity updated successfully'
        ], 200);
    }

    public function updateTempSessionItemQuantity(Request $request)
    {
        // $request->validate([
        //     'user_id' => 'required|string',
        //     'product_id' => 'required|string',
        //     'quantity' => 'required|integer|min:1',
        // ]);

        DB::update(
            'UPDATE temp_checkout_sessions SET quantity = ?, updated_at = ? WHERE session_id = ?',
            [$request->quantity, now(), $request->session_id]
        );

        return response()->json([
            'status' => true,
            'message' => 'Buy Now item quantity updated successfully'
        ], 200);
    }

    public function getCartCount(Request $request){
        $user = session('user');
        $userId = $user['id'] ?? null;
        $count = 0;
        $guest_id = request()->cookie('guest_cart_id');

        // DB::listen(function ($query) {
        // dd($query->sql, $query->bindings, $query->time);
        // });

        if($userId != ''){
            $countResult = DB::select('SELECT COUNT(*) as count FROM cart_items WHERE user_id = ?', [$userId]);
            $count = $countResult[0]->count ?? 0;
        }
        else {
            $countResult = DB::select('SELECT COUNT(*) as count from carts where cart_id = ?',[$guest_id]);
            $count = $countResult[0]->count ?? 0;
        }

        //echo $count; exit;
        return response()->json(['count' => $count], 200);
    }

    public function deleteCookie(){
        cookie()->queue(Cookie::forget('guest_cart_id'));
        return response()->json(['status' => 'success'], 200);
    }
}
