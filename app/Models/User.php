<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function wishlist()
    {
        return $this->hasMany(Whistlist::class, 'user_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function categories()
    {
        return $this->hasMany(MerchantCategory::class);
    }

    public function cart()
    {
        return $this->hasMany(Cart::class, 'user_id');
    }

    public function address()
    {
        return $this->hasMany(DeliveryAddress::class)->select('id', 'longitude', 'latitude', 'location', 'address');
    }
    public function category()
    {
        return $this->belongsTo(MerchantCategory::class, 'category_id');
    }


    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_to');
    }

    public function discounts()
    {
        return $this->hasOne(Discount::class, 'user_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    // Define the relationship with Order model for orders as a merchant
    public function merchantOrders()
    {
        return $this->hasMany(Order::class, 'merchant_id');
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class);
    }
}
