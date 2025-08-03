<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'grade_id' => $this->grade_id,
            'img' => $this->img,
            'nameOfBook' => $this->nameOfBook,
            'price' => $this->price,
            'description' => $this->description,
            'creationDate' => $this->creationDate,
            'grade' => new GradeResource($this->whenLoaded('grade')),
        ];
    }
}
