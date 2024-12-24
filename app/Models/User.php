<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Function to return the register validation rules
     * @return array rules
     */
    public static function get_rules_register(){
        return[
        "name"=> "required|string",
        "email"=> "required|unique:users|string|email",
        "password"=> "required|string|min:8",
        ];
    }

    /**
     * Function to return the login validation rules
     * @return array rules
     */
    public static function get_rules_login(){
        return [
            "email"=> "required|email",
            "password"=>'required'
        ];
    }

    /**
     * Get orders from User
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get User's Shopping Cart
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function shoppingCart()
    {
        return $this->hasOne(ShoppingCart::class);
    }
}
