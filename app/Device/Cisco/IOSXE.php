<?php

namespace App\Device\Cisco;

class IOSXE extends \App\Device\Cisco\Cisco
{
    protected static $singleTableType = __CLASS__;

    /*
    This method is used to determine the TYPE of Cisco\IOSXE device this is and recategorize it.
    This is the end of the discovery line for this type of device.
    Instead of running another discovery, this will perform a scan() and return the object.
    Returns App\Device\Cisco\IOSXE object;
    */
    public function discover()
    {
        print __CLASS__ . "\n";
        $this->scan();
        return $this;
    }
}