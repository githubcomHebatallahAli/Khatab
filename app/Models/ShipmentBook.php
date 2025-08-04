<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentBook extends Model
{
    protected $fillable = [
        'book_id',
        'shipment_id',
        'quantity',
        'price'
    ];
}
