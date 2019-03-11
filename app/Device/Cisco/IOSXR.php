<?php

namespace App\Device\Cisco;

class IOSXR extends \App\Device\Cisco\Cisco
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

    public function getSerial()
    {
        $reg = "/SN:\s+(\S+)/";
        if (preg_match($reg, $this->data['inventory'], $hits))
        {
            return $hits[1];
        }
    }

    public function getModel()
    {
        $reg = "/(\S+)\s+Chassis/";
        if (preg_match($reg, $this->data['version'], $hits))
        {
            return $hits[1];
        }
    }
}
