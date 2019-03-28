<?php

namespace App\Http\Resources\Device;

use Illuminate\Http\Resources\Json\Resource;

class DeviceCollection extends Resource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //return parent::toArray($request);

        // Return a custom return for each object in the collection. We do this by extending resource
        return [
            'id'     => $this->id,
            'ip'     => $this->ip,
            'type'   => $this->type,
            'name'   => $this->name,
            'model'  => $this->model,
            'serial' => $this->serial,
            'href'   => [
                'link' => route('device.show', $this->id),
            ],
        ];
    }
}
