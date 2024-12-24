<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ShoppingCart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\Product;

class ShoppingCartController extends Controller
{
    /**
     * Get user's shopping cart
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show(Request $request){
        try{
        //Get the current user
        $user=auth()->user();

        //No pagination since there is just one shopping cart per user.
        //Obtain the product cart.
        $shoppingCart = ShoppingCart::with('cartItems')->where("user_id",$user->id)->firstOrFail();
        
        //200 should be returned if everything goes well
        return response()->json($shoppingCart,200);

        }catch (ModelNotFoundException $e){
            //Log this in Laravel.log for us
            Log::error("Error getting shopping cart: ". $e->getMessage(),[
                "stack"=> $e->getTraceAsString(),
            ]);
            return response()->json([
            "error"=> "Shopping cart not found.",
            "message"=> $e->getMessage()
            ], 404);
        }catch (\Throwable $th){
            //Log this in Laravel.log for us
            Log::error("Error getting products: ". $th->getMessage(),[
                "stack"=> $th->getTraceAsString(),
            ]);

            //Show this as a response to the user, or to the webpage
            return response()->json([
                "error"=>"Error getting shopping cart: ",
                "message"=> $th->getMessage()
            ], 500);
        }
    }

    /**
     * Save a product to the Shopping cart, it creates a shopping cart if it doesn't exist
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request){
        try{
            //VariantID y cantidad.
            $validator = Validator::make($request->all(),ShoppingCart::get_rules_add_product());
            if ($validator->fails()){
                //Show this as a response to the user, or to the webpage
                return response()->json([
                    "message"=>"Error validating product data",
                    'errors'=> $validator->errors(),
                ], 422);
            }
            //Let's see first if the product exists, else, fail
            $var_id=$request->variant_id;
            $variant=ProductVariant::findorFail($var_id);

            //Get the user from token and stuff
            $user=auth()->user();
            //Create a shopping cart if it is empty, to store the cart items, or something
            $shopping_cart= ShoppingCart::with('cartItems')->where("user_id",$user->id)->firstOrCreate([
                "user_id"=>$user->id,
                "status"=>"created"
            ]);   
            $cart_items= $shopping_cart->cartItems;      

            // Check for duplicate variant_ids
            $duplicates = $cart_items->filter(function ($item) use ($var_id) {
                return $item->product_variant_id == $var_id;
            });
            // Return if there is already the same variant id in cart 
            if ($duplicates->isNotEmpty()) {
                return response()->json([
                    "message"=>"Variant already in cart!"
                ],422);
            }

            //See if quantity is greater than what is in stock
            if($request->quantity > $variant->stock_quantity){
                return response()->json([
                    "message"=> "Quantity greater than stock!"
                ],422);
            }

            //Create the cartItem, linking it to the user's shopping cart
            $price=Product::find($variant->product_id)->price;

            $cart_item=CartItem::create([
                "shopping_cart_id"=> $shopping_cart->id,
                "product_variant_id"=> $variant->id,
                "quantity"=> $request->quantity,
                "unit_price"=>$price
            ]);
            $cart_item->load('variant');

            return response()->json($cart_item,201);
        }catch (ModelNotFoundException $e){
            //Log this in Laravel.log for us
            Log::error("Error getting product variant: ". $e->getMessage(),[
                "variant_id"=> $request->variant_id,
                "stack"=> $e->getTraceAsString(),
            ]);
            return response()->json([
                "error"=> "Product variant not found",
                "message"=> $e->getMessage()
            ], 404);
        }catch (\Throwable $th){
            //Log this in Laravel.log for us
            Log::error("Error creating cart item: ". $th->getMessage(),[
                "stack"=> $th->getTraceAsString(),
            ]);
    
            //Show this as a response to the user, or to the webpage
            return response()->json([
                "error"=>"Error creating cart item.",
                "message"=> $th->getMessage()
            ], 500);
        }
    }

    /**
     * Update cart Item quantity
     * @param \Illuminate\Http\Request $request
     * @param int $CartItemID
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $CartItemID){
        try{
            //Get the user from token and stuff
            $user=auth()->user();
            //Im doing this so that if there isn't a cart, technically you could say that there isn't a cart item with that id
            //And voila, instead of having two errors, we have one :)))))
            $shopping_cart= ShoppingCart::with('cartItems')->where("user_id",$user->id)->first();         
            $cart_item=$shopping_cart->cartItems->findorFail($CartItemID);

            $variant=ProductVariant::find($cart_item->product_variant_id);
            //See if quantity is greater than what is in stock
            if($request->quantity > $variant->stock_quantity){
                return response()->json([
                    "message"=> "Quantity greater than stock!"
                ],422);
            }

            $cart_item->update([
                "quantity"=> $request->quantity
            ]);
            //show variant!
            $cart_item->load("variant");
            return response()->json($cart_item,200);
        }catch (ModelNotFoundException $e){
            //Log this in Laravel.log for us
            Log::error("Error getting cart item: ". $e->getMessage(),[
                "cart_item_id"=> $CartItemID,
                "stack"=> $e->getTraceAsString(),
            ]);
            return response()->json([
                "error"=> "Cart Item not found",
                "message"=> $e->getMessage()
            ], 404);
        }catch (\Throwable $th){
            //Log this in Laravel.log for us
            Log::error("Error creating cart item: ". $th->getMessage(),[
                "stack"=> $th->getTraceAsString(),
            ]);
    
            //Show this as a response to the user, or to the webpage
            return response()->json([
                "error"=>"Error creating cart item.",
                "message"=> $th->getMessage()
            ], 500);
        }
    }

    /**
     * Delete cart item
     * @param int $CartItemID
     * @return mixed|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function destroy(int $CartItemID){
        try{
            $user=auth()->user();
            //We find the user shopping cart
            $shopping_cart= ShoppingCart::with('cartItems')->where("user_id",$user->id)->first();         
            $cart_item=$shopping_cart->cartItems->findorFail($CartItemID);

            $cart_item->delete();
            return response()->noContent();
        }catch (ModelNotFoundException $e){
            //Log this in Laravel.log for us
            Log::error("Error getting cart item: ". $e->getMessage(),[
                "cart_item_id"=> $CartItemID,
                "stack"=> $e->getTraceAsString(),
            ]);
            return response()->json([
                "error"=> "Cart Item not found",
                "message"=> $e->getMessage()
            ], 404);
        }catch (\Throwable $th){
            //Log this in Laravel.log for us
            Log::error("Error deleting cart item: ". $th->getMessage(),[
                "stack"=> $th->getTraceAsString(),
            ]);
    
            //Show this as a response to the user, or to the webpage
            return response()->json([
                "error"=>"Error deleting cart item.",
                "message"=> $th->getMessage()
            ], 500);
        }
    }
    
}
