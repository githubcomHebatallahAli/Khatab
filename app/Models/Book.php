<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
     use HasFactory, SoftDeletes;
    const storageFolder= 'Book';
    protected $fillable = [
        'grade_id',
        'img',
        'nameOfBook',
        'price',
        'description',
        'creationDate',
    ];

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function carts()
    {
        return $this->belongsToMany(Cart::class, 'cart_books')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }

    public function shipments()
    {
        return $this->belongsToMany(Shipment::class, 'shipment_books')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }

  
}
