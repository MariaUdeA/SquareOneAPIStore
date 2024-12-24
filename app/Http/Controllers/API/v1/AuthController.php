<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Models\User;


class AuthController extends Controller
{
    /**
     * User register function with brief validation
     * @param \Illuminate\Http\Request $request contains name, email and password
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request){
        try{
            $validator = Validator::make($request->all(),User::get_rules_register());

            //It is not necessary to create an exception
            //It would be weird if everytime a user gets some input wrong we get a log
            if ($validator->fails()){
                //Show this as a response to the user, or to the webpage
                return response()->json([
                    "message"=>"Error validating user data",
                    'errors'=> $validator->errors(),
                ], 422);
            }
            
            //Crear el usuario si pasa la validación
            $user=User::create([
                "name"=> $request["name"],
                "email"=> $request["email"],
                "password"=> Hash::make($request["password"]),
            ]);

            return response()->json([
                "user"=>$user,
                "message"=>"User registered Successfully"
            ],201);
        }catch(\Throwable $th){
            //This is literaly just in case because I'm not certain how to get another error than
            //the authetication error from the middleware
            Log::error("Error registering user: ". $th->getMessage(),[
                "stack"=> $th->getTraceAsString(),
            ]);

            //Show this as a response to the user, or to the webpage
            return response()->json([
                "error"=>"Error registering user",
                "message"=> $th->getMessage()
            ], 500);
        }
    }
    

    /**
     * Login function with brief validation
     * @param \Illuminate\Http\Request $request minimum email and password
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
        try{
            $validator = Validator::make($request->all(),User::get_rules_login());
            //It is not necessary to create an exception
            //It would be weird if everytime a user gets some input wrong we get a log
            if ($validator->fails()){
                //Show this as a response to the user, or to the webpage
                return response()->json([
                    "message"=>"Error validating user data",
                    'errors'=> $validator->errors(),
                ], 422);
            }

            $user=User::where('email',$request->email)->first();
        
            if(!$user || !Hash::check($request->password,$user->password)){
                return response()->json([
                    "message"=>"The provided credentials were incorrect."
                ],401);
            }

            $token=$user->createToken("auth_token")->plainTextToken;

            return response()->json([
                'user'=>[
                "id"=>$user->id,
                "name"=>$user->name,
                "email"=>$user->email],
                "token"=>$token
            ],200);

        }catch(\Throwable $th){
            //This is literaly just in case because I'm not certain how to get another error than
            //the authetication error from the middleware
            Log::error("Error logging in: ". $th->getMessage(),[
                "stack"=> $th->getTraceAsString(),
            ]);

            //Show this as a response to the user, or to the webpage
            return response()->json([
                "error"=>"Error logging in: ",
                "message"=> $th->getMessage()
            ], 500);
        }
    }

    /**
     * Get user information, must be logged in
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function profile(){
        try{
            $user=auth()->user();

            return response()->json([
                'user'=>[
                "id"=>$user->id,
                "name"=>$user->name,
                "email"=>$user->email],
            ],200);
        }catch(\Throwable $th){
            //This is literaly just in case because I'm not certain how to get another error than
            //the authetication error from the middleware
            Log::error("Error getting user: ". $th->getMessage(),[
                "stack"=> $th->getTraceAsString(),
            ]);

            //Show this as a response to the user, or to the webpage
            return response()->json([
                "error"=>"Error getting user",
                "message"=> $th->getMessage()
            ], 500);
        }
    }
    
    /**
     * Logout function, must be logged in
     * @param \Illuminate\Http\Request $request this has got to have the tokens, or something
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request){
        try{
            //Revoke current user token $user->tokens()->where('id', $user->currentAccessToken()->id)->delete()
            
            //Get logged in user
            $user=auth()->user();

            // Revoke all tokens...
            $user->tokens()->delete();

		    // Revoke the current token
            $user->currentAccessToken()->delete();

            return response()->json([
                'message'=>'Logged out successfully :)',
            ],200);

        }catch(\Throwable $th){
            //This is literaly just in case because I'm not certain how to get another error than
            //the authetication error from the middleware
            Log::error("Error logging out user: ". $th->getMessage(),[
                "stack"=> $th->getTraceAsString(),
            ]);

            //Show this as a response to the user, or to the webpage
            return response()->json([
                "error"=>"Error logging out user",
                "message"=> $th->getMessage()
            ], 500);
        }
    }

}
