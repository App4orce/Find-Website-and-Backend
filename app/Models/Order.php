<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Define the relationship with User model for merchant_id
    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderDetail::class);
    }

    
}
