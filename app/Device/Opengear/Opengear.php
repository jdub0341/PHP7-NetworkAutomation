<?php

namespace App\Device\Opengear;

use phpseclib\Net\SSH2;

class Opengear extends \App\Device\Device
{ 
    protected static $singleTableSubclasses = [
    ];

    protected static $singleTableType = __CLASS__;

    //List of commands to run during a scan of this device.
    public $cmds = [
        ''                  =>  '/etc/scripts/support_report.sh',
        'run'               =>  'config -g config',
        'version'           =>  'cat /etc/version',
        'support_report'    =>  'cat /etc/config/support_report',
        'serial'            =>  'showserial',
    ];

    /*
    This method is used to establish a CLI session with a device.
    It will attempt to use phpseclib\Net\SSH2 library to connect.
    Returns a phpseclib\Net\SSH2 object.
    */
    public function getCli()
    {
        $credentials = $this->getCredentials();
        foreach($credentials as $credential)
        {
            $deviceinfo = [
                "host"      =>  $this->ip,
                "username"  =>  $credential->username,
                "password"  =>  $credential->passkey,
            ];
            // Attempt to connect using Metaclassing\SSH library.
            try
            {
                $cli = new SSH2($deviceinfo['host']);
                if ($cli->login($deviceinfo['username'], $deviceinfo['password']))
                {
                    $this->credential_id = $credential->id;
                    $this->save();
                    return $cli;
                }
            } catch (\Exception $e) {
                
            }
        }
    }

    /*
    This method is used to determine the TYPE of Opengear device this is and recategorize it.
    This is the end of the discovery line for this type of device.
    Instead of running another discovery, this will perform a scan() and return the object.
    Returns App\Device\Opengear\Opengear object;
    */ 
    public function discover()
    {
        print __CLASS__ . "\n";
        $this->scan();
        return $this;
    }

    /*
    Find the name of this device from DATA.
    Returns string (device name).
    */
    public function getName()
    {
        $reg = "/config.system.name (\S+)/";
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
        return $this->data['serial'];
    }

    /*
    Find the model of this device from DATA.
    Returns string (device model).
    */
    public function getModel()
    {
        $reg = "/<model>(\S+)<\/model>/";
        if(preg_match($reg,$this->data['support_report'], $hits))
        {
            return $hits[1];
        }
    }


}
