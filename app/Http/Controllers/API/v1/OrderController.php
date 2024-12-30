<?php

namespace App\Http\Controllers\API\v1;

use App\Models\CartItem;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;


use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShoppingCart;

class OrderController extends Controller
{
    /**
     * Get orders for logged in user
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try{
            $user=auth()->user();
            $orders= Order::with('orderItems')->where("user_id",$user->id)->get();         
            if ($orders->isEmpty()){
                return response()->json([
                    "message"=>"No orders found."
                ],404);
            }
            return response()->json($orders,200);
        }catch(\Throwable $th){
            //This is literaly just in case because I'm not certain how to get another error than
            //the authetication error from the middleware
            Log::error("Error getting orders: ". $th->getMessage(),[
                "stack"=> $th->getTraceAsString(),
            ]);

            //Show this as a response to the user, or to the webpage
            return response()->json([
                "error"=>"Error getting orders",
                "message"=> $th->getMessage()
            ], 500);
        }
    }

    /**
     * Save shopping cart in a new order.
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try{
            $validator = Validator::make($request->all(),[
                "payment_method"=>"required|string",
                "shipping_address"=>"required|string"
            ]);

            //It is not necessary to create an exception
            //It would be weird if everytime a user gets some input wrong we get a log
            if ($validator->fails()){
                //Show this as a response to the user, or to the webpage
                return response()->json([
                    "message"=>"Error validating user data",
                    'errors'=> $validator->errors(),
                ], 422);
            }

            $user=auth()->user();
            $cart=ShoppingCart::with("cartItems")->where("user_id",$user->id)->firstorFail();
            $cart_items=$cart->cartItems()->get();

            if($cart_items->isEmpty()){
                return response()->json([
                    "message"=> "This cart is empty! Nothing to order!"
                    ],400);
            }

            $total_amount=$cart_items->sum(function($cart_item) {
                return $cart_item->unit_price*$cart_item->quantity;
            });

            $order=Order::create([
                "user_id"=> $user->id,
                "order_date"=>now(),
                "total_amount"=>$total_amount,
                "order_status"=> $cart->status,
                'payment_method' => $request->payment_method,
                'shipping_address' => $request->shipping_address
            ]);

            foreach ($cart_items as $cart_item){
                OrderItem::create([
                    'order_id'=> $order->id,
                    'product_variant_id'=> $cart_item->product_variant_id,
                    "quantity" => $cart_item->quantity,
                    "price"=> $cart_item->unit_price,
                ]);
            }

            //Now we delete the shopping cart and its cart items :)
            $cart_items=CartItem::where("shopping_cart_id",$cart->id);
            $cart->delete();
            $cart_items->delete();

            DB::commit();
            return response()->json($order->load("orderItems"),201);

        }catch (ModelNotFoundException $e){
            DB::rollBack();
            //Log this in Laravel.log for us
            Log::error("Error getting order: ". $e->getMessage(),[
                "stack"=> $e->getTraceAsString(),
            ]);
            return response()->json([
            "error"=> "Cart not found.",
            "message"=> $e->getMessage()
            ], 404);
        }catch(\Throwable $th){
            DB::rollBack();
            //This is literaly just in case because I'm not certain how to get another error than
            //the authetication error from the middleware
            Log::error("Error getting order: ". $th->getMessage(),[
                "stack"=> $th->getTraceAsString(),
            ]);

            //Show this as a response to the user, or to the webpage
            return response()->json([
                "error"=>"Error getting orders",
                "message"=> $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display specific order yay!
     * @param string $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try{
            $order=Order::with("orderItems")->findorFail($id);
            if(!Gate::allows("view-order", $order)){
                return response()->json([
                    "message"=> "No rights to see this content."],
                    403);
            }
            return response()->json($order,200);
        }catch (ModelNotFoundException $e){
            //Log this in Laravel.log for us
            Log::error("Error getting order: ". $e->getMessage(),[
                "stack"=> $e->getTraceAsString(),
            ]);
            return response()->json([
            "error"=> "Order not found.",
            "message"=> $e->getMessage()
            ], 404);
        }catch(\Throwable $th){
            //This is literaly just in case because I'm not certain how to get another error than
            //the authetication error from the middleware
            Log::error("Error getting order: ". $th->getMessage(),[
                "stack"=> $th->getTraceAsString(),
            ]);

            //Show this as a response to the user, or to the webpage
            return response()->json([
                "error"=>"Error getting orders",
                "message"=> $th->getMessage()
            ], 500);
        }
    }

}
