<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        "shopping_cart_id",
        "product_variant_id",
        "quantity",
        "unit_price",
    ];

    /** @use HasFactory<\Database\Factories\CartItemFactory> */
    use HasFactory;

    public function cart(){
        return $this->belongsTo(ShoppingCart::class);
    }

    public function variant(){
        return $this->belongsTo(ProductVariant::class, 'product_variant_id', 'id');
    }
}
