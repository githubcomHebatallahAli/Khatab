<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
   

    protected $fillable = ['session_id'];

    public function books()
    {
        return $this->belongsToMany(Book::class, 'cart_books')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }

    public function addBook(Book $book, $quantity = 1)
    {
        $this->books()->attach($book->id, [
            'quantity' => $quantity,
            'price' => $book->price
        ]);
    }

     public function updateBookQuantity(Book $book, $quantity)
    {
        if ($this->books()->where('book_id', $book->id)->exists()) {
            $this->books()->updateExistingPivot($book->id, ['quantity' => $quantity]);
        }
    }

    public function removeBook(Book $book)
    {
        $this->books()->detach($book->id);
    }

    public function clearCart()
    {
        $this->books()->detach();
    }

    public function totalPrice()
    {
        return $this->books->sum(function ($book) {
            return $book->pivot->quantity * $book->pivot->price;
        });
    }



    public function getTotalQuantity()
    {
        return $this->books->sum('pivot.quantity');
    }

    public function getTotalPriceFormatted()
    {
        return number_format($this->totalPrice(), 2, '.', '');
    }

    public function getCartSummary()
    {
        return [
            'total_quantity' => $this->getTotalQuantity(),
            'total_price' => $this->getTotalPriceFormatted(),
            'book_count' => $this->bookCount(),
            'final_price' => $this->getFinalPrice()
        ];
    }


    public function getFinalPrice()
    {
        $shipping = 10;
        $tax = 0;
        $total = $this->totalPrice();
        return number_format($total + $shipping + $tax, 2, '.', '');
    }

}
