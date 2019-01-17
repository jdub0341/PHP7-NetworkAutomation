<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use SoftDeletes;

    public function discover()
    {
        //put code here to discover more specific class
    }

    public function deleteMe()
    {

    }

    public function scan()
    {

    }
    
}
