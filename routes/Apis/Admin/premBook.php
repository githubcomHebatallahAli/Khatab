<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PremBookController;


Route::controller(PremBookController::class)
->prefix('/admin')
->middleware(['admin'])
->group(
    function () {
    Route::get('/showAll/premBook','showAll');
    Route::get('/edit/premBook/{id}','edit');
   Route::post('/create/premBook', 'create');
   Route::post('/update/premBook/{id}', 'update');
   Route::delete('/delete/premBook/{id}', 'destroy');
   Route::get('/showDeleted/premBook', 'showDeleted');
Route::get('/restore/premBook/{id}','restore');
Route::delete('/forceDelete/premBook/{id}','forceDelete');
});


Route::controller(premBookController::class)
->group(
    function () {
    Route::get('/showAll/premBook','userShowAll');
    Route::get('/edit/premBook/{id}','userEdit');
});