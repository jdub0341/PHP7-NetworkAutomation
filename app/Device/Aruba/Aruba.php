<?php

namespace App\Device\Aruba;

class Aruba extends \App\Device\Device
{
    //protected static $singleTableSubclasses = [];
    protected static $singleTableType = __CLASS__;

    public function discover()
    {
        $this->save();
        print __CLASS__ . "\n";
    }
}
