<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthorResource extends JsonResource
{
    public function toArray($request)
    {
        return array_merge([
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
        ], $this->whenLoaded('books', function () {
            return [
                'books' => BookCollection::make($this->books),
            ];
        }, []));
    }
}