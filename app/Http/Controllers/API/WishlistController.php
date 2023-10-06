<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Wishlist;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlists = auth()->user()->wishlists;
        return response()->json($wishlists);
    }

    public function store(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $wishlist = new Wishlist([
                'user_id' => auth()->id(),
                'name' => $request->input('name'),
            ]);
            $wishlist->save();
            $response = [
                "success" => true,
                "data" => $wishlist
            ];
    
            return response()->json($response, 201);
        } catch (ValidationException $e) {
            $response = [
                "success" => false,
                "message" => $e->getMessage(),
            ];
            return response()->json($response, 404);
        }
    }

    public function show(Wishlist $wishlist)
    {
        if ($wishlist->user_id === auth()->id()) {
            return response()->json($wishlist);
        } else {
            return response()->json(['message' => 'Wishlist not found.'], 404);
        }
    }

    public function update(Request $request, Wishlist $wishlist)
    {
        if ($wishlist->user_id !== auth()->id()) {
            return response()->json(['message' => 'Wishlist not found.'], 404);
        }
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $wishlist->name = $request->input('name');
            $wishlist->save();
            $response = [
                "success" => true,
                "data" => $wishlist
            ];
    
            return response()->json($response, 201);
        } catch (ValidationException $e) {
            $response = [
                "success" => false,
                "message" => $e->getMessage(),
            ];
            return response()->json($response, 404);
        }
    }

    public function destroy(Wishlist $wishlist)
    {
        if ($wishlist->user_id !== auth()->id()) {
            return response()->json(['message' => 'Wishlist not found.'], 404);
        }

        $wishlist->delete();

        return response()->json(['message' => 'Wishlist deleted.']);
    }
}
