<?php


use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;




Route::middleware('web')
    ->controller(CartController::class)
    ->prefix('/user')
    ->group(function () {
        Route::get('/show/his/cart', 'show');
        Route::post('/add/book', 'addBook');
        Route::post('/update/book/quantity', 'updateBookQuantity');
        Route::post('/remove/book', 'removeBook');
        Route::post('/clear/cart', 'clearCart');
    });