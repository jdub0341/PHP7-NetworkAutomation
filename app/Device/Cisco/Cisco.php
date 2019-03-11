<?php

namespace App\Device\Cisco;

use Metaclassing\SSH;

class Cisco extends \App\Device\Device
{ 
    protected static $singleTableSubclasses = [
        IOS::class,
        IOSXE::class,
        IOSXR::class,
        NXOS::class,
    ];
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
        $cli->exec('terminal length 0');
        return $cli;
    }

    public function discover()
    {
        print __CLASS__ . "\n";

        $match = [
            '\App\Device\Cisco\IOS'     =>  0,
            '\App\Device\Cisco\IOSXE'   =>  0,
            '\App\Device\Cisco\IOSXR'   =>  0,
            '\App\Device\Cisco\NXOS'    =>  0,
        ];

        $regex = [
            '\App\Device\Cisco\IOS'     => [
                "/cisco ios software/i",
            ],
            '\App\Device\Cisco\IOSXE'   => [
                "/ios-xe/i",
                "/package:/i",
            ],
            '\App\Device\Cisco\IOSXR'   => [
                "/ios xr/i",
                "/iosxr/i",
            ],
            '\App\Device\Cisco\NXOS'    => [
                "/Cisco Nexus/i",
                "/nx-os/i",
            ],
        ];

        $cli = $this->getCli();

        $commands = [
            'sh version',
            'sh version running',
        ];

        foreach($commands as $command)
        {
            $output = $cli->exec($command);
            foreach($regex as $class => $regs)
            {
                foreach($regs as $reg)
                {
                    if(preg_match($reg,$output))
                    {
                        $match[$class]++;
                    }
                }
            }
        }
        arsort($match);
        $tmp = array_keys($match);
        $newtype = reset($tmp);
        $cli->disconnect();
        return $this->convertType($newtype)->discover();
    }

    public function scan()
    {
        $cli = $this->getCli();
        $data = $this->data;

        $data['run'] = $cli->exec("sh run");
        $data['version'] = $cli->exec("sh version");
        $data['interfaces'] = $cli->exec("sh interfaces");
        $data['inventory'] = $cli->exec("sh inventory");
        $data['dir'] = $cli->exec("dir");

        $data['cdp'] = $cli->exec("sh cdp neighbor");
        $data['lldp'] = $cli->exec("sh lldp neighbor");

        $this->data = $data;
        $this->name = $this->getName();
        $this->serial = $this->getSerial();
        $this->model = $this->getModel();

        $this->save();
    }

    public function getName()
    {
        $reg = "/hostname (\S+)/";
        if(preg_match($reg,$this->data['run'], $hits))
        {
            return $hits[1];
        }
    }

    public function getSerial()
    {
        $reg = "/^Processor board ID (\S+)/m";
        if (preg_match($reg, $this->data['version'], $hits))
        {
            $serial = $hits[1];
        }
        return $serial;
    }

    public function getModel()
    {
        if (preg_match('/.*isco\s+(WS-\S+)\s.*/', $this->data['version'], $reg))
        {
        $model = $reg[1];

        return $model;
        }
        if (preg_match('/.*isco\s+(OS-\S+)\s.*/', $this->data['version'], $reg))
        {
            $model = $reg[1];

            return $model;
        }
        if (preg_match('/.*ardware:\s+(\S+),.*/', $this->data['version'], $reg))
        {
            $model = $reg[1];

            return $model;
        }
        if (preg_match('/.*ardware:\s+(\S+).*/', $this->data['version'], $reg))
        {
            $model = $reg[1];

            return $model;
        }
        if (preg_match('/^[c,C]isco\s(\S+)\s\(.*/m', $this->data['version'], $reg))
        {
            $model = $reg[1];

            return $model;
        }
    }

}
