<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $orders = auth()->user()->orders;
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'description' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $order = new Order([
                'user_id' => auth()->id(),
                'order_number' => uniqid(),
                'description' => $request->input('description'),
            ]);
            $order->save();
            $response = [
                "success" => true,
                "data" => $order
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

    public function show(Order $order)
    {
        if ($order->user_id === auth()->id()) {
            return response()->json(['success' => false, 'data' => $order]);
        } else {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }
    }

    public function update(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        try {
            $validator = Validator::make($request->all(), [
                'description' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $order->description = $request->input('description');
            $order->save();
            $response = [
                "success" => true,
                "data" => $order
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

    public function destroy(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json(['success' => false,'message' => 'Order not found.'], 404);
        }
        $order->delete();
        return response()->json(['success' => true,'message' => 'Order deleted.']);
    }
}
