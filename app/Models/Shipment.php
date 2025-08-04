<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'description',
        'subtotal',
        'shipping',
        'tax',
        'total',
        'creationDate'
    ];

    public function books()
    {
        return $this->belongsToMany(Book::class, 'shipment_books')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }

    public function totalPrice()
    {
        return $this->books->sum(function ($book) {
            return $book->pivot->quantity * $book->pivot->price;
        });
    }

  
}
