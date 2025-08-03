<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartBook extends Model
{
    protected $fillable = [
        'cart_id',
        'book_id',
        'quantity',
        'price'
    ];

 
}
