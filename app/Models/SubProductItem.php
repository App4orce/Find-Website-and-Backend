<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubProductItem extends Model
{
    use HasFactory;
    public function cartItem()
    {
        return $this->belongsTo(CartItem::class);
    }

    public function product()
    {
        return $this->belongsTo(SubProduct::class);
    }
}
