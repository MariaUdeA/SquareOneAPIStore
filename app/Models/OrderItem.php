<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    /** @use HasFactory<\Database\Factories\OrderItemsFactory> */
    use HasFactory;
    public function order(){
        return $this->belongsTo(Order::class);
    }
    public function variant(){
        return $this->hasOne(ProductVariant::class);
    }
}
