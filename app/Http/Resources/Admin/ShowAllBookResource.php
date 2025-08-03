<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowAllBookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nameOfBook' => $this->nameOfBook,
            'img' => $this->img,
            'price' => $this->price,
            'grade' => new GradeResource($this->whenLoaded('grade')),
          
        ];
    }
}
