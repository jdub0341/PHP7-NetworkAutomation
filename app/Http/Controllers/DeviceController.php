<?php

namespace App\Http\Controllers;

use App\Http\Resources\Device\DeviceCollection; 
use App\Http\Resources\Device\DeviceResource; 
use App\Device\Device;
use Illuminate\Http\Request;

use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\Filter;

class DeviceController extends Controller
{
	public function __construct()
	{
		//Require Authentication for all APIs except index and show 
		//$this->middleware('auth:api');
	}
	
	/**
	* @SWG\Get(
	*     path="/api/device",
	*     tags={"Device"},
	*     summary="Get Device",
	*     description="",
	*     operationId="",
	*     consumes={"application/json"},
	*     produces={"application/json"},
	*     @SWG\Parameter(
	*         name="include",
	*         in="query",
	*         description="relationships to include (Comma seperated)",
	*         required=false,
	*         type="string"
	*     ),
	*     @SWG\Parameter(
	*         name="filter[id]",
	*         in="query",
	*         description="id of device",
	*         required=false,
	*         type="string"
	*     ),
	*     @SWG\Parameter(
	*         name="filter[ip]",
	*         in="query",
	*         description="ip of device",
	*         required=false,
	*         type="string"
	*     ),
	*     @SWG\Parameter(
	*         name="filter[type]",
	*         in="query",
	*         description="type of device",
	*         required=false,
	*         type="string"
	*     ),
	*     @SWG\Parameter(
	*         name="filter[name]",
	*         in="query",
	*         description="name of device",
	*         required=false,
	*         type="string"
	*     ),
	*     @SWG\Parameter(
	*         name="filter[model]",
	*         in="query",
	*         description="model of device",
	*         required=false,
	*         type="string"
	*     ),
	*     @SWG\Parameter(
	*         name="filter[serial]",
	*         in="query",
	*         description="serial of device",
	*         required=false,
	*         type="string"
	*     ),
	*     @SWG\Response(
	*         response=200,
	*         description="successful operation",
	*     ),
	*     security={
	*         {"AzureAD": {}},
	*     }
	* )
	**/
    public function index(Request $request)
    {
        if($request->paginate)
        {
            $paginate = $request->paginate;
        } else {
            $paginate = env("DEFAULT_PAGINATION");
        }
		$devices = QueryBuilder::for(Device::class)
			->allowedFilters(Filter::exact('id'),Filter::exact('ip'),Filter::exact('type'),Filter::exact('name'),Filter::exact('model'),Filter::exact('serial'))
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
	* @SWG\Get(
	*     path="/api/device/{id}",
	*     tags={"Device"},
	*     summary="Get Device by ID",
	*     description="",
	*     operationId="",
	*     consumes={"application/json"},
	*     produces={"application/json"},
	*     @SWG\Parameter(
	*         name="id",
	*         in="path",
	*         description="ID of Device",
	*         required=true,
	*         type="integer"
	*     ),
	*     @SWG\Response(
	*         response=200,
	*         description="successful operation",
	*     ),
	*     @SWG\Response(
	*         response="401",
	*         description="Unauthorized user",
	*     ),
	*     security={
	*         {"AzureAD": {}},
	*     }
	* )
	**/
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function destroy(Device $device)
    {
        //
    }
	
	
	/**
	* @SWG\Get(
	*     path="/api/device/search/{search}",
	*     tags={"Device"},
	*     summary="Get Device by Search",
	*     description="",
	*     operationId="",
	*     consumes={"application/json"},
	*     produces={"application/json"},
	*     @SWG\Parameter(
	*         name="search",
	*         in="path",
	*         description="search",
	*         required=true,
	*         type="string"
	*     ),
	*     @SWG\Response(
	*         response=200,
	*         description="successful operation",
	*     ),
	*     @SWG\Response(
	*         response="401",
	*         description="Unauthorized user",
	*     ),
	*     security={
	*         {"AzureAD": {}},
	*     }
	* )
	**/
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
