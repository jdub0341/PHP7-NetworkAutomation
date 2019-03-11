<?php

namespace App\Device\Cisco;

class IOS extends \App\Device\Cisco\Cisco
{
 
    //protected static $singleTableSubclasses = [];
    protected static $singleTableType = __CLASS__;

    public function discover()
    {
        print __CLASS__ . "\n";
        $this->save();
        $this->scan();
        return $this;
    }
}
