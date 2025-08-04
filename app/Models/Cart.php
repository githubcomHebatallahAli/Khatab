<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
   use HasFactory;

    protected $fillable = ['session_id'];

    protected float $shipping = 10.0;
    protected float $tax = 0.0;

    public function books()
    {
        return $this->belongsToMany(Book::class, 'cart_books')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }

    public function addBook(Book $book, $quantity = 1)
    {
        $this->books()->syncWithoutDetaching([
            $book->id => [
                'quantity' => $quantity,
                'price' => $book->price,
            ]
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
        return $this->books()->detach();
    }

    public function totalPrice()
    {
        return $this->books->sum(function ($book) {
            return $book->pivot->quantity * $book->pivot->price;
        });
    }

    public static function findCartBySessionId($sessionId)
    {
        return self::where('session_id', $sessionId)->first();
    }

    public static function createCart($sessionId)
    {
        return self::create(['session_id' => $sessionId]);
    }

    public static function findOrCreateCart($sessionId)
    {
        return self::findCartBySessionId($sessionId) ?? self::createCart($sessionId);
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
    $subtotal = $this->totalPrice();
    $shipping = $this->shipping;
    $tax = $this->tax;
    $final = $subtotal + $shipping + $tax;

    return [
        'total_quantity' => $this->getTotalQuantity(),
        'book_count'     => $this->bookCount(),
        'subtotal'       => number_format($subtotal, 2, '.', ''),
        'shipping'       => number_format($shipping, 2, '.', ''),
        'tax'            => number_format($tax, 2, '.', ''),
        'final_price'    => number_format($final, 2, '.', '')
    ];
}


    public function getFinalPrice()
    {
        return number_format($this->totalPrice() + $this->shipping + $this->tax, 2, '.', '');
    }

    public function getCartDetails()
    {
        return [
            'books' => $this->books->map(function ($book) {
                return [
                    'id'       => $book->id,
                    'title'    => $book->title,
                    'price'    => $book->pivot->price,
                    'quantity' => $book->pivot->quantity,
                    'total'    => $book->pivot->price * $book->pivot->quantity,
                ];
            }),
            'summary' => $this->getCartSummary()
        ];
    }

    public function hasBook(Book $book)
    {
        return $this->books->contains($book->id);
    }

    public function bookCount()
    {
        return $this->books->count();
    }

    public function getSessionId()
    {
        return $this->session_id;
    }

    public function setSessionId($sessionId)
    {
        $this->session_id = $sessionId;
        $this->save();
    }

}
