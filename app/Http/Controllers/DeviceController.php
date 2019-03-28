<?php

namespace App\Http\Controllers;

use App\Device\Device;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\Filter;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\Device\DeviceResource;
use App\Http\Resources\Device\DeviceCollection;

class DeviceController extends Controller
{
	public function __construct()
	{
		//Require Authentication for all APIs except index and show 
		$this->middleware('auth:api');
	}
	

	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->paginate) {
            $paginate = $request->paginate;
        } else {
            $paginate = env('DEFAULT_PAGINATION');
        }
        $devices = QueryBuilder::for(Device::class)
            ->allowedFilters(Filter::exact('id'), Filter::exact('ip'), Filter::exact('type'), Filter::exact('name'), Filter::exact('model'), Filter::exact('serial'))
            //->allowedIncludes('part','vendor','warranty')
            ->paginate($paginate);

        return DeviceCollection::collection($devices);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

	/**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Device $device)
    {
        return new DeviceResource($device);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Model\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function edit(Device $device)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Device $device)
    {
        $device->update($request->all());
		return new DeviceResource($device); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $device = Device::findOrFail($id);
		$device->delete();
		return new DeviceResource($device); 
    }
	
	/**
     * Seach for text in the listing resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	public function search(Request $request)
    {
		if($request->paginate)
        {
            $paginate = $request->paginate;
        } else {
            $paginate = env("DEFAULT_PAGINATION");
        }
		
		$search = $request->search; 
		
		// Use Laravel Scout to do the search inside its scheduled indexes. 
		$devices = Device::search($search)->paginate($paginate);
		
		// If nothting is returned from Scout then use Elequent Like to do the search inside data.
		if(!$devices){
			$devices = Device::where('data', 'like', '%' .$search. '%')->paginate($paginate);
		}
		
		return DeviceCollection::collection($devices); 
    }
}
