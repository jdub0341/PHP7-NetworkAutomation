<?php

namespace App\Device\Cisco;

class IOSXR extends \App\Device\Cisco\Cisco
{
    protected static $singleTableType = __CLASS__;

    /*
    This method is used to determine the TYPE of Cisco\IOSXR device this is and recategorize it.
    This is the end of the discovery line for this type of device.
    Instead of running another discovery, this will perform a scan() and return the object.
    Returns App\Device\Cisco\IOSXR object;
    */
    public function discover()
    {
        print __CLASS__ . "\n";
        $this->scan();
        return $this;
    }

    /*
    Find the serial of this device from DATA.
    Returns string (device serial).
    */
    public function getSerial()
    {
        //Reg to grab the serial from the show inventory.
        $reg = "/SN:\s+(\S+)/";
        if (preg_match($reg, $this->data['inventory'], $hits))
        {
            return $hits[1];
        }
    }

    /*
    Find the model of this device from DATA.
    Returns string (device model).
    */
    public function getModel()
    {
        //Reg to grab the model from the show version.
        $reg = "/(\S+)\s+Chassis/";
        if (preg_match($reg, $this->data['version'], $hits))
        {
            return $hits[1];
        }
    }
}
