<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    /**
     * Fillable data for products
     * @var array
     */
    protected $fillable = [
        "name",
        "description",
        "price",
        "other_attributes",
    ];

    /**
     * Automatic casting for columns
     * @var array
     */
    protected $casts = [
        // Other attributes with automatic casting
        'other_attributes' => 'array', // If you just want to automatically decode JSON
        //this is from the github 10/10 solved all my problems
    ];

    /**
     * Mutator to convert other attributes to json
     * @param mixed $value
     * @return void
     */
    public function setOtherAttributesAttribute($value){
        $this->attributes["other_attributes"] = json_encode($value);
    }


    /**
     * Get product variants
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
