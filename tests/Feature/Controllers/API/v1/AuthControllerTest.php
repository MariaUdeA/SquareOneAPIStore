<?php

namespace Tests\Feature\Controllers\Api\v1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class AuthControllerTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');
    }

    public function test_create_user_route(){
        //create user
        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.ex',
            'password' => 'password',
        ]);
        //Check if the user was created successfully
        $response->assertStatus(201);
    }
    
    public function test_login_user_route(){
        //Log in
        $user = User::factory()->create();
        
        $response = $this->postJson('/api/v1/login',[
            'email' => $user->email,
            'password' => "password"
            ]
        );
        //Check for correct login
        $response->assertStatus(200);

        //Create token for the next operations
        $token = $user->createToken('Test Token');

        //See if we can see the user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
            ])->getJson('/api/v1/profile');
        //Check for correct operation
        $response->assertStatus(200);

        //Log out!!
        // Send the request with the Authorization header
        $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token->plainTextToken,
                ])->postJson('/api/v1/logout');
        
        $response->assertStatus(200);

    }

    /*public function test_negative_user_route() {
        Product::factory()->hasVariants(3)->count(5)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);
    }*/
}