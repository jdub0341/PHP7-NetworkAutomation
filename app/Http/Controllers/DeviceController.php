<?php

namespace App\Http\Controllers;

use App\Device\Device as Model;
use Illuminate\Http\Request;
//use Spatie\QueryBuilder\Filter;
//use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\Device\DeviceResource as Resource;
use App\Http\Resources\Device\DeviceCollection as ResourceCollection;
use App\Queries\DeviceQuery as Query;

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
        $user = auth()->user();
		if ($user->cant('read', Model::class)) {
			abort(401, 'You are not authorized');
        }

        //Apply proper queries and retrieve a LengthAwarePaginator object.
        $paginator = Query::apply($request);
        //Create a new ResourceCollection object.
        //return new ResourceCollection($paginator);
/*         $rc = new ResourceCollection($paginator);
        print_r($rc);
        return $rc; */

        //Save the Collection to a tmp variable
        $tmp = $paginator->getCollection();
        //Create a new ResourceCollection object.
        $resource = new ResourceCollection($paginator);
        //Overwrite the resource collection so that it is proper type of Collection Type;
        $resource->collection = $tmp;
        return $resource;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = auth()->user();
		if ($user->cant('create', Model::class)) {
			abort(401, 'You are not authorized');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Model $device)
    {
        $user = auth()->user();
		if ($user->cant('read', Model::class)) {
			abort(401, 'You are not authorized');
        }

        return new Resource($device);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Model\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function edit(Model $device)
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
    public function update(Request $request, Model $device)
    {
        $user = auth()->user();
		if ($user->cant('update', Model::class)) {
			abort(401, 'You are not authorized');
        }

        $device->update($request->all());

        return new Resource($device);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = auth()->user();
		if ($user->cant('delete', Model::class)) {
			abort(401, 'You are not authorized');
        }

        $device = Model::findOrFail($id);
        $device->delete();

        return new Resource($device);
    }

    /**
     * Seach for text in the listing resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        if ($request->paginate) {
            $paginate = $request->paginate;
        } else {
            $paginate = env('DEFAULT_PAGINATION');
        }

        $search = $request->search;

        // Use Laravel Scout to do the search inside its scheduled indexes.
        $devices = Model::search($search)->paginate($paginate);

        // If nothting is returned from Scout then use Elequent Like to do the search inside data.
        if (! $devices) {
            $devices = Model::where('data', 'like', '%'.$search.'%')->paginate($paginate);
        }

        return ResourceCollection::collection($devices);
    }
}
