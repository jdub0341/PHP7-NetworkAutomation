<?php

namespace App\Queries;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\Filter;
use App\Device\Device as Model;

class DeviceQuery
{
    public static function apply(Request $request, $object = null)
    {
        $defaultColumns = [
            'id',
            'type',
            'ip',
            'name',
            'credential_id',
            'vendor',
            'model',
            'serial',
            'deleted_at',
            'created_at',
            'updated_at',
            'data',
        ];

        $query = QueryBuilder::for(Model::class);

        if($request->has('select'))
        {
            $array = explode(",",$request->select);
            foreach($array as $select)
            {
                if(in_array(strtolower($select),Model::getColumns()))
                {
                    $query->addselect($select);
                }
            }
        } else {
            foreach($defaultColumns as $column)
            {
                $query->addselect($column);
            }
        }

        if($request->has('data'))
        {
            $array = explode(",",$request->data);
            foreach($array as $data)
            {
                $query->addselect('data->' . $data . ' as ' . $data);
            }
        }

        $query->allowedFilters([
                'name',
                'model',
                Filter::exact('ip'),
                Filter::exact('serial'),
                Filter::exact('credential_id'),
            ])
		    ->allowedIncludes([
            ])
            ->allowedSorts([
                'id',
                'ip',
                'name',
                'model',
                'serial',
                'created_at',
                'updated_at',
            ])
            ->defaultSort('id');

            if($object)
            {
                $query->where('id',$object->id);
            }

        $paginator = $query->paginate($request->paginate ?: env('DEFAULT_PAGINATION'))->appends(request()->query());

        return $paginator;
    }
}