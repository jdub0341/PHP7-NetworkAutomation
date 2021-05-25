<?php

namespace App\Queries;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Device\Device as Model;

class DeviceQuery
{
    public static function apply(Request $request, $object = null)
    {
		$query = QueryBuilder::for(Model::class)
            ->allowedFilters([
                'ip',
                'name',
                'model',
                //AllowedFilter::exact('site_id'),
                //AllowedFilter::exact('default_room_id'),
                //AllowedFilter::exact('contact_id'),
            ])
		    ->allowedIncludes([
                //'site',
                //'address',
                //'contact',
                //'rooms',
                //'defaultRoom',
            ])
            ->allowedSorts([
                'id',
                'ip',
                'name',
                'model',
            ])
            ->defaultSort('id');

            if($object)
            {
                $query->where('id',$object->id);
            }

        //$collection = $query->get();
        //$collection = $query->paginate($request->paginate ?: env('DEFAULT_PAGINATION'), 'page', $request->page);
        $paginator = $query->paginate($request->paginate ?: env('DEFAULT_PAGINATION'))->appends(request()->query());

        //return $collection;
        return $paginator;
    }
}