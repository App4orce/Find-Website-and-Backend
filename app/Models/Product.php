<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    public function subItems()
    {
        return $this->hasMany(SubProduct::class)->select('id', 'product_id', 'name', 'status', 'price');
    }

    public function subProductItems()
    {
        return $this->hasMany(SubProductItem::class,'sub_product_id');
    }
    
    
}
