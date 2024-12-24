<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingCart extends Model
{
    /** @use HasFactory<\Database\Factories\ShoppingCartFactory> */
    use HasFactory;
    protected $fillable = [
        "user_id",
        "status"
    ];

    public static function get_rules_add_product(){
        return[
        "variant_id"=> "required|integer",
        "quantity"=> "required|integer",
        ];
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function cartItems(){
        return $this->hasMany(CartItem::class);
    }

}
