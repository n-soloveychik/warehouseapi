<?php

namespace App\Http\Resources;

use App\Models\MountingType;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MountingTypeCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function (MountingType $type){
            return $type->only(['id', 'type']);
        })->toArray();
    }
}
