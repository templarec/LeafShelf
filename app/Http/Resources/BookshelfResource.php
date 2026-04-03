<?php

namespace App\Http\Resources;

use App\Models\Bookshelf;
use Illuminate\Http\Resources\Json\JsonResource;

class BookshelfResource extends JsonResource
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
            'key' => 'bookshelf-' . $this->id,
            'label' => $this->name,
            'icon' => 'pi pi-fw pi-server',
            'selectable' => $this->selectable,
            'model' => Bookshelf::class,
            'books_count' => (int) ($this->books_count ?? 0),
            'children' => ShelfCollection::make(
                $this->whenLoaded('shelves')
            )->selectable($this->selectable),
        ];
    }
}