<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    // ✅ Get all cart items for a specific user
    public function getCartItems($user_id)
    {
        //echo 'reached here @#'; exit;
        $items = DB::select('SELECT * FROM cart_items WHERE user_id = ?', [$user_id]);

        return response()->json([
            'status' => true,
            'items' => $items ?? []
        ], 200);
    }

    // ✅ Add item to cart or update existing item quantity
    public function addToCart(Request $request)
    {
        //print_r($request->all()); exit;
        //echo 'reached here @#'; exit;
        // $request->validate([
        //     'user_id' => 'required|string',
        //     'product_id' => 'required|string',
        //     'name' => 'required|string',
        //     'description' => 'nullable|string',
        //     'price' => 'required|numeric',
        //     'quantity' => 'required|integer|min:1',
        //     'image' => 'required|string',
        // ]);

        // Check if the product already exists in the cart for this user
        $existingItem = DB::select(
            'SELECT * FROM cart_items WHERE user_id = ? AND product_id = ? LIMIT 1',
            [$request->user_id, $request->product_id]
        );

        if ($existingItem) {
            // ✅ Update quantity if item already exists
            DB::update(
                'UPDATE cart_items 
                 SET quantity = quantity + ?, updated_at = ? 
                 WHERE user_id = ? AND product_id = ?',
                [1, now(), $request->user_id, $request->product_id]
            );

            $updatedItem = DB::select(
                'SELECT * FROM cart_items WHERE user_id = ? AND product_id = ? LIMIT 1',
                [$request->user_id, $request->product_id]
            );

            return response()->json([
                'status' => true,
                'message' => 'Cart item quantity updated',
                'item' => $updatedItem[0]
            ], 200);
        } else {
            //echo 'reached here !2'; exit;
            // Get product Details
            //DB::enableQueryLog();
            
            $productDetails = DB::select(
                'SELECT * FROM products WHERE _id = ? LIMIT 1',
                [$request->product_id]
            );
            // Get the executed queries
            //$queries = DB::getQueryLog();

            //dd($queries);
            //print_r($productDetails[0]->name);exit;

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

            // ✅ Insert new item if not exists
            DB::insert(
                'INSERT INTO cart_items (user_id, product_id, name, description, price, quantity, image, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $request->user_id,
                    $request->product_id,
                    $productDetails[0]->name,
                    $productDetails[0]->description,
                    $productDetails[0]->price,
                    1,
                    $singleImage,
                    now(),
                    now()
                ]
            );

            $newItem = DB::select(
                'SELECT * FROM cart_items WHERE user_id = ? AND product_id = ? LIMIT 1',
                [$request->user_id, $request->product_id]
            );

            return response()->json([
                'status' => true,
                'message' => 'Item added to cart successfully',
                'item' => $newItem[0]
            ], 201);
        }
    }

    // ✅ Remove an item from the cart
    public function removeFromCart(Request $request)
    {
        //print_r($request->all()); exit;
        // $request->validate([
        //     'user_id' => 'required|string',
        //     'product_id' => 'required|string',
        // ]);
        //echo $request->user_id; exit;

        DB::delete(
            'DELETE FROM cart_items WHERE user_id = ? AND product_id = ?',
            [$request->user_id, $request->product_id]
        );

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

        DB::update(
            'UPDATE cart_items SET quantity = ?, updated_at = ? WHERE user_id = ? AND product_id = ?',
            [$request->quantity, now(), $request->user_id, $request->product_id]
        );

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
}
