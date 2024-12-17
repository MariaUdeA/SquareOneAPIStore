<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    /** @use HasFactory<\Database\Factories\CartItemFactory> */
    use HasFactory;

    public function cart(){
        return $this->belongsTo(ShoppingCart::class);
    }

    public function variant(){
        return $this->hasOne(ProductVariant::class);
    }
}
