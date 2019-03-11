<?php

namespace App\Device\Aruba;

use Metaclassing\SSH;

class Aruba extends \App\Device\Device
{
    //protected static $singleTableSubclasses = [];
    protected static $singleTableType = __CLASS__;

    public function getCli()
    {
        $deviceinfo = $this->generateDeviceInfo();
        // Create a ssh object with our device information
        try{
            $cli = new SSH($deviceinfo);
            $cli->connect();
        } catch (\Exception $e) {

        }

        // send the term len 0 command to stop paging output with ---more---
        $cli->exec('no paging');
        return $cli;
    }

    public function discover()
    {
        print __CLASS__ . "\n";
        $this->save();
        $this->scan();
        return $this;
    }

    public function scan()
    {
        $cli = $this->getCli();
        $data = $this->data;

        $data['run'] = $cli->exec("sh run");
        $data['version'] = $cli->exec("sh version");
        $data['inventory'] = $cli->exec("sh inventory");
        $data['dir'] = $cli->exec("dir");
        $data['lldp'] = $cli->exec("sh lldp neighbor");

        $this->data = $data;
        $this->name = $this->getName();
        $this->serial = $this->getSerial();
        $this->model = $this->getModel();

        $this->save();
        return $this;
    }

    public function getName()
    {
        $reg = "/hostname\s+\"(\S+)\"/";
        if(preg_match($reg,$this->data['run'], $hits))
        {
            return $hits[1];
        }
    }

    public function getSerial()
    {
        $reg = "/System\s+Serial#\s+:\s+(\S+)/";
        if (preg_match($reg, $this->data['inventory'], $hits))
        {
            return $hits[1];
        }
    }

    public function getModel()
    {
        $reg = "/SC\sModel#\s+:\s+(\S+)/";
        if (preg_match($reg, $this->data['inventory'], $hits))
        {
            return $hits[1];
        }
    }
}
