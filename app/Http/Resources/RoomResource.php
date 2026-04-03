<?php

namespace App\Http\Resources;

use App\Models\Room;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
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
            'key' => 'room-' . $this->id,
            'label' => $this->name,
            'icon' => 'pi pi-fw pi-stop',
            'selectable' => $this->selectable,
            'model' => Room::class,
            'books_count' => (int) ($this->books_count ?? 0),
            'children' => BookshelfCollection::make(
                $this->whenLoaded('bookshelves')
            )->selectable($this->selectable),
        ];
    }
}