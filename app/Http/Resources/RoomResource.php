<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Resources\Json\JsonResource;

    class RoomResource extends JsonResource
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
                'key' => '0-0-0',
                'label' => $this->name,
                'data' => $this->name,
                'icon' => 'pi pi-fw pi-stop',
                'children' => [BuildingResource::make($this->buildings)],
                $this->mergeWhen($this->whenLoaded('buildings'), [
                    'children' => [BuildingResource::make($this->buildings)],
                ]),
            ];
        }
    }
