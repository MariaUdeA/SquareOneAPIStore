<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShoppingCart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //First, we create the shopping cart that creates users since it is one to one
        ShoppingCart::factory(50)->create();
        //We create new users
        User::factory(100)->create();
        //Creating products table
        Product::factory(50)->create();
        //this one creates variants so we do it first
        CartItem::factory(100)->create();
        //add some more
        ProductVariant::factory(50)->create();
        //Create orders
        Order::factory(200)->create();
        //creating order items
        OrderItem::factory(300)->create();
    }
}
