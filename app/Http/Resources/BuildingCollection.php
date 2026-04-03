<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BuildingCollection extends ResourceCollection
{
    protected $selectable;

    public function selectable($value)
    {
        $this->selectable = $value;

        return $this;
    }

    public function toArray($request)
    {
        return $this->collection->map(function (BuildingResource $resource) use ($request) {
            return $resource->selectable($this->selectable)->toArray($request);
        })->all();
    }
}