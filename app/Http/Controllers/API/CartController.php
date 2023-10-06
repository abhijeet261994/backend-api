<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;
use App\Models\Cart;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $productId = $request->input('product_id');
            $quantity = $request->input('quantity', 1);

            Cart::updateorCreate(['user_id' => auth()->id(), 'product_id' => $productId],
            [
                'user_id' => auth()->id(),
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
            
            $response = [
                "success" => true,
                "message" => "Product added to cart successfully",
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

    public function removeFromCart(Cart $cart)
    {
        if ($cart->user_id !== auth()->id()) {
            return response()->json(['success' => false,'message' => 'Product not found in cart'], Response::HTTP_NOT_FOUND);
        }
        $cart->delete();
        return response()->json(['success' => true,'message' => 'Product removed from the cart']);
    }

    public function checkout(Request $request)
    {
        // Validate the request and get necessary data
        $this->validate($request, [
            'order_id' => 'required|integer',
            'customer_email' => 'required|email',
            'amount' => 'required|numeric',
        ]);
        
        // Create a payment intent using the payment service
        $paymentIntentResponse = Http::post('https://fakedata.nanocorp.io/api/payment/create', [
            'order_id' => $request->input('order_id'),
            'customer_email' => $request->input('customer_email'),
            'amount' => $request->input('amount'),
        ]);
        
        // Check if the payment intent was successfully created
        if ($paymentIntentResponse->successful()) {
            $paymentIntent = $paymentIntentResponse->json();
            
            // Use the payment intent to complete the order
            $confirmPaymentResponse = Http::post('https://fakedata.nanocorp.io/api/payment/confirm', [
                'payment_intend' => $paymentIntent['data']['payment_intend'], // Use the correct key from the response
            ]);
            
            if ($confirmPaymentResponse->successful()) {
                return response()->json(['message' => 'Payment successful']);
            } else {
                return response()->json(['message' => 'Payment confirmation failed'], 500);
            }
        } else {
            return response()->json(['message' => 'Payment intent creation failed'], 500);
        }
    }
}
