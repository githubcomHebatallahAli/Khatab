<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BookController;


Route::controller(BookController::class)
->prefix('/admin')
->middleware(['admin'])
->group(
    function () {
    Route::get('/showAll/book','showAll');
    Route::get('/edit/book/{id}','edit');
   Route::post('/create/book', 'create');
   Route::post('/update/book/{id}', 'update');
   Route::delete('/delete/book/{id}', 'destroy');
   Route::get('/showDeleted/book', 'showDeleted');
Route::get('/restore/book/{id}','restore');
Route::delete('/forceDelete/book/{id}','forceDelete');
});

