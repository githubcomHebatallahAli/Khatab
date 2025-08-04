<?php


use App\Http\Controllers\User\CartController;
use Illuminate\Support\Facades\Route;



Route::controller(CartController::class)
->prefix('/user')
->group(
    function () {
   Route::get('/show/his/cart', 'show');
   Route::post('/add/book', 'addBook');
   Route::post('/update/book/quantity/book', 'updateBookQuantity');
    Route::post('/remove/book', 'removeBook');
    Route::post('/clear/cart', 'clearCart');
 



});