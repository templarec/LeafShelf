<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Resources\Json\JsonResource;

    class ShelfResource extends JsonResource
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
                'root' => [
                    'id' => $this->id,
                    'key' => "0",
                    'label' => $this->name,
                    'data' => $this->name,
                    'icon' => 'pi pi-fw pi-book',
                    'children' => [BookshelfResource::make($this->bookshelves)],
                    $this->mergeWhen($this->whenLoaded('bookshelves'), [
                        'children' => [BookshelfResource::make($this->bookshelves)],
                    ]),

                ],
            ];
        }
    }
