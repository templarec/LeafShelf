<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'isbn' => $this->ISBN,
            'title' => $this->title,
            'publisher' => $this->publisher,
            'pages' => $this->pages,
            'img' => $this->img,
            'authors' => AuthorCollection::make($this->whenLoaded('authors')),
            'location' => $this->whenLoaded('shelf', function () {
                return [
                    'building' => $this->shelf?->bookshelf?->room?->building?->name,
                    'room' => $this->shelf?->bookshelf?->room?->name,
                    'bookshelf' => $this->shelf?->bookshelf?->name,
                    'shelf' => $this->shelf?->name,
                    'shelf_id' => $this->shelf?->id,
                ];
            }),
        ];
    }
}