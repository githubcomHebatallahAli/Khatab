<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Book;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // عرض تفاصيل السلة
    public function show(Request $request)
    {
        $sessionId = $request->session()->getId();
        $cart = Cart::findCartBySessionId($sessionId);
        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }
        return response()->json($cart->getCartDetails());
    }

   
    public function addBook(Request $request)
    {
        $sessionId = $request->session()->getId();
        $bookId = $request->input('book_id');
        $quantity = $request->input('quantity', 1);

        $cart = Cart::findCartBySessionId($sessionId);
        if (!$cart) {
            $cart = Cart::createCart($sessionId);
        }

        $book = Book::find($bookId);
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        if ($cart->hasBook($book)) {
            $cart->updateBookQuantity($book, $quantity);
        } else {
            $cart->addBook($book, $quantity);
        }

        return response()->json(['message' => 'Book added to cart', 'cart' => $cart->getCartDetails()]);
    }

    // حذف كتاب من السلة
    public function removeBook(Request $request)
    {
        $sessionId = $request->session()->getId();
        $bookId = $request->input('book_id');

        $cart = Cart::findCartBySessionId($sessionId);
        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $book = Book::find($bookId);
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $cart->removeBook($book);
        return response()->json(['message' => 'Book removed from cart', 'cart' => $cart->getCartDetails()]);
    }

    // تفريغ السلة
    public function clear(Request $request)
    {
        $sessionId = $request->session()->getId();
        $cart = Cart::findCartBySessionId($sessionId);
        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }
        $cart->clearCart();
        return response()->json(['message' => 'Cart cleared', 'cart' => $cart->getCartDetails()]);
    }
}

