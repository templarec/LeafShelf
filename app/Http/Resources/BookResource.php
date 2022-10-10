<?php

    namespace App\Http\Resources;

    use App\Models\Author;
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
                'publisher' => $this->pubisher,
                'pages' => $this->pages,
                'authors' => AuthorCollection::make($this->authors),
                $this->mergeWhen($this->whenLoaded('authors'), [
                    'authors' => AuthorCollection::make($this->authors),
                ]),
                'location' => ShelfResource::make($this->shelves),
                $this->mergeWhen($this->whenLoaded('shelves'), [
                    'location' => ShelfResource::make($this->shelves),
                ]),

            ];
        }

    }
