<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AuthorCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return $this->collection->map(function (AuthorResource $resource) use ($request) {
            return $resource->toArray($request);
        })->all();
    }
}