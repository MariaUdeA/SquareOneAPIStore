<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\True_;
use function Pest\Laravel\json;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


use App\Models\Product;
use App\Models\ProductVariant;

class ProductController extends Controller
{

    /**
     * Get all products
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request){
        try{
            //Gets the number of products per page, default is 10.
            //Can be written /products?per_page=number
            $perPage=$request->query("per_page",10);

            //Obtain all products with variants, and paginate by the number
            //Of pages specified before.
            $products=Product::with("variants")->paginate($perPage);

            //Return 404 if there are no products
            if ($products->isEmpty()) 
                return response()->json(["message"=> "Products not found :("],404);
            
            //200 should be returned if everything goes well
            return response()->json($products,200);

        }catch (\Throwable $th){
            //Log this in Laravel.log for us
            Log::error("Error getting products: ". $th->getMessage(),[
                "stack"=> $th->getTraceAsString(),
            ]);

            //Show this as a response to the user, or to the webpage
            return response()->json([
                "error"=>"Error getting products",
                "message"=> $th->getMessage()
            ], 500);
        }
    }

    /**
     * Show products by ID
     * @param int $id id of the product
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show(int $id){
        try{
            //Find product by id
            $product=Product::with("variants")->findOrFail($id);
            return response()->json($product,200);
        }
        catch (ModelNotFoundException $e){
            //Log this in Laravel.log for us
            Log::error("Error getting product: ". $e->getMessage(),[
                "product_id"=> $id,
                "stack"=> $e->getTraceAsString(),
            ]);
            return response()->json([
            "error"=> "Product not found",
            "message"=> $e->getMessage()
            ], 404);
        } catch (\Throwable $th){
            //Log this in Laravel.log for us
            Log::error("Error getting product: ". $th->getMessage(),[
                "stack"=> $th->getTraceAsString(),
            ]);

            //Show this as a response to the user, or to the webpage
            return response()->json([
                "error"=>"Error getting product",
                "message"=> $th->getMessage()
            ], 500);
        }
    }

    /**
     * Save a singular product with variants
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request){
        //Start transaction
        DB::beginTransaction();
        try {

            //Save product to database.
            $productData = $request->only(['name', 'description', 'price', 'other_attributes']);
            $product = Product::create($productData);
    
           // Save the associated variants
            if ($request->has('variants')) {
                $variants = $request->input('variants');
                foreach ($variants as $variant) {
                    ProductVariant::create([
                        'product_id'=> $product->id,
                        'color'=> $variant['color'],
                        'size'=> $variant['size'],
                        'stock_quantity'=> $variant['stock_quantity'],

                    ]);
                }
            }
    
            //If no error has occurred, the transaction for product and the transaction for variants are saved.
            DB::commit();
    
            return response()->json($product->load('variants'), 201); // Return the product with the variants
        }catch(\Throwable $th){
            DB::rollBack();
            //This is literaly just in case because I'm not certain how to get another error than
            //the authetication error from the middleware
            Log::error("Error creating product: ". $th->getMessage(),[
                "stack"=> $th->getTraceAsString(),
            ]);

            //Show this as a response to the user, or to the webpage
            return response()->json([
                "error"=>"Error creating product",
                "message"=> $th->getMessage()
            ], 500);
        }
    }

    /**
     * Update product TO DO:Update with variants
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id){
        DB::beginTransaction();
        try {
            
            //Find Product in Database.
            $product = Product::findOrFail($id);

            //Update the product found with the data sent in the body of the request.
            $product->update($request->all());
            
            $product_variants=$product->variants();

            if ($request->has('variants')) {
                $variants = $request->input('variants');
                foreach ($variants as $variant) {
                    if($variant["id"] != null){
                        $var_upt=$product_variants->findOrFail($variant["id"]);
                        $var_upt->update($variant);
                    }
                }
            }
            DB::commit();
            return response()->json($product->load('variants'), 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Product not found',
                'message' => $e->getMessage()
            ], 404);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error Getting products',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Delete product and its associated variants
     * @param int $id
     * @return mixed|\Illuminate\Http\Response
     */
    public function destroy(int $id){
        try{
            $product=Product::findOrFail($id);
            $variants=ProductVariant::where('product_id', $product->id);
            $product->delete();
            $variants->delete();
            return response()->noContent();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Product not found',
                'message' => $e->getMessage()
            ], status: 404);

        } catch (\Throwable $th) {

            return response()->json([
                'error' => 'Error Getting product',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Search item by parameters
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function search(Request $request){
        try{    
            $query=Product::with('variants');
            //name filter
            if($request->has("name")){
                $query=Product::where("name","like","%".$request->input("name")."%");
            }
            //price filter
            if($request->has("min_price")){
                $query=Product::where("price",">=",$request->input("min_price"));
            }
            if($request->has("max_price")){
                $query=Product::where("price","<=",$request->input("max_price"));
            }
            //Brand, collection and gender filter
            if($request->has("attributes") && $request->has("value")){
                $attributes= $request->input("attributes");
                $value= $request->input("value");
                $query=Product::whereJsonContains("other_attributes->". $attributes,$value);
            }
            //Color filter
            if ($request->has('color')) {
                $color = $request->input('color');
                $query->whereHas('variants', function (Builder $q) use ($color) {
                    $q->where('color', $color);
                });
            }
            //Size filter
            if ($request->has('size')) {
                $size = $request->input('size');
                $query->whereHas('variants', function (Builder $q) use ($size) {
                    $q->where('size', $size);
                });
            }

            if ($query->get()->isEmpty()) {
                return response()->json([
                    "message"=>"No products found.",
                ],404);
            }
            $products =$query->paginate();
            return response()->json($products,200);
        }catch (\Throwable $th) {
            return response()->json([
                'error' => 'Error Getting filtered products',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
