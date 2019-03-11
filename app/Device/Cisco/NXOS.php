<?php

namespace App\Device\Cisco;

class NXOS extends \App\Device\Cisco\Cisco
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
        $reg = "/PID:\s+(\S+)/";
        if (preg_match($reg, $this->data['inventory'], $hits))
        {
            return $hits[1];
        }
    }


}
