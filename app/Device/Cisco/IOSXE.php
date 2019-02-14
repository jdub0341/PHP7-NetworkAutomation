<?php

namespace App\Device\Cisco;

class IOSXE extends \App\Device\Cisco\Cisco
{
 
    //protected static $singleTableSubclasses = [];
    protected static $singleTableType = __CLASS__;

    public function discover()
    {
        $this->save();
        print __CLASS__ . "\n";

    }
}
