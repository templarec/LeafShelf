<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RoomCollection extends ResourceCollection
{
    protected $selectable;

    public function selectable($value)
    {
        $this->selectable = $value;

        return $this;
    }

    public function toArray($request)
    {
        return $this->collection->map(function (RoomResource $resource) use ($request) {
            return $resource->selectable($this->selectable)->toArray($request);
        })->all();
    }
}