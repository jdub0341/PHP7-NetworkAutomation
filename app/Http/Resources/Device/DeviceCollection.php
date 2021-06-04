<?php

namespace App\Http\Resources\Device;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DeviceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        if($request->has('parsed'))
        {
            //print_r($this->collection);
            $this->collection->parsed();
        }

/*         if($request->has('data'))
        {
            //print_r($this->collection);
            $this->collection->withoutData();
        } */

        return [
            'data'  =>  $this->collection,
        ];




        //return parent::toArray($request);

        // Return a custom return for each object in the collection. We do this by extending resource
/*
        return [
            'id'     => $this->id,
            'ip'     => $this->ip,
            'type'   => $this->type,
            'name'   => $this->name,
            'model'  => $this->model,
            'serial' => $this->serial,
            'data'   => $this->data,
            'href'   => [
                'link' => route('device.show', $this->id),
            ],
        ];
        */
    }
}
