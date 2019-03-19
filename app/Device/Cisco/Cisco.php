<?php

namespace App\Device\Cisco;

use Metaclassing\SSH;
use DB;

class Cisco extends \App\Device\Device
{ 
    protected static $singleTableSubclasses = [
        IOS::class,
        IOSXE::class,
        IOSXR::class,
        NXOS::class,
    ];
    protected static $singleTableType = __CLASS__;

    //List of commands to run during a scan of this device.
    public $cmds = [
        'run'           =>  'sh run',
        'version'       =>  'sh version',
        'interfaces'    =>  'sh interfaces',
        'inventory'     =>  'sh inventory',
        'dir'           =>  'dir',
        'cdp'           =>  'sh cdp neighbor',
        'lldp'          =>  'sh lldp neighbor',
    ];

    /*
    This method is used to establish a CLI session with a device.
    It will attempt to use Metaclassing\SSH library to work with specific models of devices that do not support ssh2.0 natively.
    Returns a Metaclassing\SSH object.
    */
    public function getCli()
    {
        $credentials = $this->getCredentials();
        foreach($credentials as $credential)
        {
            // Attempt to connect using Metaclassing\SSH library.
            try
            {
                $cli = $this->getSSH1($this->ip, $credential->username, $credential->passkey);
            } catch (\Exception $e) {
                //If that fails, attempt to connect using phpseclib\Net\SSH2 library.
            }
            if($cli)
            {
                $this->credential_id = $credential->id;
                $this->save();
                return $cli;
            }
        }
    }

    /*
    This method is used to determine the TYPE of Cisco device this is and recategorize it.
    Once recategorized, it will perform discover() again.
    Returns null;
    */
    public function discover()
    {
        print __CLASS__ . "\n";
        $this->save();
        //list of available Cisco devices types initialized to 0
        $match = [
            'App\Device\Cisco\IOS'     =>  0,
            'App\Device\Cisco\IOSXE'   =>  0,
            'App\Device\Cisco\IOSXR'   =>  0,
            'App\Device\Cisco\NXOS'    =>  0,
        ];

        //Different regex to use to classify device.
        $regex = [
            'App\Device\Cisco\IOS'     => [
                "/cisco ios software/i",
            ],
            'App\Device\Cisco\IOSXE'   => [
                "/ios-xe/i",
                "/package:/i",
            ],
            'App\Device\Cisco\IOSXR'   => [
                "/ios xr/i",
                "/iosxr/i",
            ],
            'App\Device\Cisco\NXOS'    => [
                "/Cisco Nexus/i",
                "/nx-os/i",
            ],
        ];

        $cli = $this->getCli();
        if(!$cli)
        {

        }
        //List of commands to run to classify device
        $commands = [
            'sh version',
            'sh version running',
        ];

        /*
        Go through each COMMAND and execute it. and see if it matches each of the $regex entries we have.
        If we find a match, +1 for that class.
        */
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
        $cli->disconnect();
        //sort the $match array so the class with the highest count is on top.
        arsort($match);
        //just grab the class names
        $tmp = array_keys($match);
        //set $newtype to the TOP class in $match.
        $newtype = reset($tmp);
        //Modify the record in the DB to change the type.
        DB::table('devices')
        ->where('id', $this->id)
        ->update(['type' => $newtype]);
        //Get a fresh copy of this model from the DB (which gives us a new class type) and immediately run discover().
        $this->fresh()->discover();
    }

    /*
    Find the name of this device from DATA.
    Returns string (device name).
    */
    public function getName()
    {
        $reg = "/hostname (\S+)/";
        if(preg_match($reg,$this->data['run'], $hits))
        {
            return $hits[1];
        }
    }

    /*
    Find the serial of this device from DATA.
    Returns string (device serial).
    */
    public function getSerial()
    {
        $reg = "/^Processor board ID (\S+)/m";
        if (preg_match($reg, $this->data['version'], $hits))
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
        if (preg_match('/.*isco\s+(WS-\S+)\s.*/', $this->data['version'], $reg))
        {
            return $reg[1];
        }
        if (preg_match('/.*isco\s+(OS-\S+)\s.*/', $this->data['version'], $reg))
        {
            return $reg[1];
        }
        if (preg_match('/.*ardware:\s+(\S+),.*/', $this->data['version'], $reg))
        {
            return $reg[1];
        }
        if (preg_match('/.*ardware:\s+(\S+).*/', $this->data['version'], $reg))
        {
            return $reg[1];
        }
        if (preg_match('/^[c,C]isco\s(\S+)\s\(.*/m', $this->data['version'], $reg))
        {
            return $reg[1];
        }
    }

}
