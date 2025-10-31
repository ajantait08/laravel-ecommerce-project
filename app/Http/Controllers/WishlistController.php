<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class WishlistController extends Controller
{
    public function storeWishlist(Request $request){

        //return $response = response()->json(['message' => 'CORS settings are correct'], 200);
        // Validate incoming data
        // $request->validate([
        //     '_id' => 'required|string',
        //     'name' => 'required|string',
        //     'user_id' => 'required|string',
        //     'user_email' => 'required|email',
        //     'action' => 'required|in:add,remove',
        // ]);

        $productId = $request->_id;
        $productName = $request->name;
        $userId = $request->user_id;
        $userEmail = $request->user_email;
        $action = $request->action;


        if ($action == 'add') {
            DB::enableQueryLog();
            DB::table('wishlists')->updateOrInsert(
                [
                    'user_id' => $userId,
                    'product_id' => $productId
                ],
                [
                    'product_name' => $productName,
                    'user_email' => $userEmail,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            return response()->json(['message' => 'Product added to wishlist']);
        }

        if ($action == 'remove') {
            DB::table('wishlists')
                ->where('user_id', $userId)
                ->where('product_id', $productId)
                ->delete();

            return response()->json(['message' => 'Product removed from wishlist']);
        }

        return response()->json(['message' => 'Invalid action'], 400);
}

public function getWishlist($userId)
{
    $wishlist = DB::table('wishlists')
        ->join('products', 'wishlists.product_id', '=', 'products._id')
        ->where('wishlists.user_id', $userId)
        ->select(
            'wishlists.product_id as _id',
            'products.name',
            'products.description',
            'products.images',
            'products.price',
            'products.offerPrice',
            'products.category',
            'wishlists.user_id',
            'wishlists.user_email',
            'wishlists.created_at'
        )
        ->orderBy('wishlists.created_at', 'desc')
        ->get();

    $sanitized = $wishlist->map(function ($item) {
        
        $images = is_string($item->images) ? json_decode($item->images, true) : $item->images;        
        $item->image[] = !empty($images) ? $images[0] : null;
        unset($item->images);
        return $item;
    });

    return response()->json(['wishlist' => $sanitized, 'ok' => true]);
}

}
