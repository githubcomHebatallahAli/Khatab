<?php


use App\Http\Controllers\Admin\ShipmentController;

use Illuminate\Support\Facades\Route;

Route::controller(ShipmentController::class)
->prefix('/user')
->group(
    function () {
        Route::post('/create/shipment', 'create');
        Route::get('/show/shipment/{id}', 'show'); 
    
   });

Route::controller(ShipmentController::class)
->prefix('/admin')
->middleware('admin')
->group(
    function () {
        
        Route::get('/edit/shipment/{id}', 'edit'); 
        Route::get('/showAll/shipments', 'showAll');
        Route::delete('/delete/shipment/{id}', 'destroy'); 
   });