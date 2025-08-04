<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function getCart(Request $request)
    {
        return Cart::findCartBySessionId($request->session()->getId());
    }

    public function updateBookQuantity(Request $request)
    {
        $bookId = $request->input('book_id');
        $quantity = $request->input('quantity');

        if (!is_numeric($quantity) || $quantity < 1) {
            return response()->json(['message' => 'Invalid quantity'], 400);
        }

        $cart = $this->getCart($request);
        if (!$cart) return response()->json(['message' => 'Cart not found'], 404);

        $book = Book::find($bookId);
        if (!$book) return response()->json(['message' => 'Book not found'], 404);

        if ($cart->hasBook($book)) {
            $cart->updateBookQuantity($book, $quantity);
            return response()->json(['message' => 'Book quantity updated', 'cart' => $cart->getCartDetails()]);
        }

        return response()->json(['message' => 'Book not in cart'], 404);
    }

    public function show(Request $request)
    {
        $cart = $this->getCart($request);
        if (!$cart) return response()->json(['message' => 'Cart not found'], 404);
        return response()->json($cart->getCartDetails());
    }

    public function addBook(Request $request)
    {
        $bookId = $request->input('book_id');
        $quantity = $request->input('quantity', 1);
        $sessionId = $request->session()->getId();

        $cart = Cart::findOrCreateCart($sessionId);

        $book = Book::find($bookId);
        if (!$book) return response()->json(['message' => 'Book not found'], 404);

        if ($cart->hasBook($book)) {
            $currentQty = $cart->books()->where('book_id', $book->id)->first()?->pivot->quantity ?? 0;
            $cart->updateBookQuantity($book, $currentQty + $quantity);
        } else {
            $cart->addBook($book, $quantity);
        }

        return response()->json(['message' => 'Book added to cart', 'cart' => $cart->getCartDetails()]);
    }

    public function removeBook(Request $request)
    {
        $bookId = $request->input('book_id');
        $cart = $this->getCart($request);
        if (!$cart) return response()->json(['message' => 'Cart not found'], 404);

        $book = Book::find($bookId);
        if (!$book) return response()->json(['message' => 'Book not found'], 404);

        $cart->removeBook($book);
        return response()->json(['message' => 'Book removed from cart', 'cart' => $cart->getCartDetails()]);
    }

    public function clear(Request $request)
    {
        $cart = $this->getCart($request);
        if (!$cart) return response()->json(['message' => 'Cart not found'], 404);

        $cart->clearCart();
        return response()->json(['message' => 'Cart cleared', 'cart' => $cart->getCartDetails()]);
    }
}
