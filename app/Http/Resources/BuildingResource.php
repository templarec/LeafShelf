<?php

namespace App\Http\Resources;

use App\Models\Building;
use Illuminate\Http\Resources\Json\JsonResource;

class BuildingResource extends JsonResource
{
    protected $selectable;

    public function selectable($value)
    {
        $this->selectable = $value;

        return $this;
    }

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'key' => 'building-' . $this->id,
            'label' => $this->name,
            'icon' => 'pi pi-fw pi-home',
            'data' => $this->name,
            'selectable' => $this->selectable,
            'model' => Building::class,
            'books_count' => (int) ($this->books_count ?? 0),
            'children' => RoomCollection::make(
                $this->whenLoaded('rooms')
            )->selectable($this->selectable),
        ];
    }
}