<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Whistlist extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'user_to'];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function restaurant()
    {
        return $this->belongsTo(User::class, 'user_to');
    }
}
