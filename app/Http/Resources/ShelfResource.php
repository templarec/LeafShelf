<?php

namespace App\Http\Resources;

use App\Models\Shelf;
use Illuminate\Http\Resources\Json\JsonResource;

class ShelfResource extends JsonResource
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
            'key' => 'shelf-' . $this->id,
            'label' => $this->name,
            'icon' => 'pi pi-fw pi-book',
            'selectable' => $this->selectable,
            'model' => Shelf::class,
            'books_count' => (int) ($this->books_count ?? 0),
            'children' => [],
        ];
    }
}