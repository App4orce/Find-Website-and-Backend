<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;
    public function restaurants()
    {
        return $this->belongsToMany(User::class);
    }

    public function benefits(){
        return $this->hasMany(PackageBenefit::class);
    }
}
