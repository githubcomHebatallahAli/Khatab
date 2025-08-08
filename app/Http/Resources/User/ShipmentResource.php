<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'description' => $this->description,
            'subtotal' => $this->subtotal,
            'shipping' => $this->shipping,
            'tax' => $this->tax,
            'total' => $this->total,
            'creationDate' => $this->creationDate,
            'books' => $this->books->map(function ($book) {
                return [
                    'id' => $book->id,
                    'nameOfBook' => $book->nameOfBook,
                    'quantity' => $book->pivot->quantity,
                    'price' => $book->pivot->price
                ];
            })
        ];

    }
}
