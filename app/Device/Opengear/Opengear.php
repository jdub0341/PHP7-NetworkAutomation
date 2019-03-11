<?php

namespace App\Device\Opengear;

use phpseclib\Net\SSH2;

class Opengear extends \App\Device\Device
{ 
    protected static $singleTableSubclasses = [
    ];

    protected static $singleTableType = __CLASS__;

    public function getCli()
    {
        $deviceinfo = $this->generateDeviceInfo();
        // Create a ssh object with our device information
        $ssh = new SSH2($deviceinfo['host']);
        if (!$ssh->login($deviceinfo['username'], $deviceinfo['password'])) {
            print "LOGIN FAILED!\n";
        }

        return $ssh;
    }

    public function discover()
    {
        $this->save();
        $this->scan();
        print __CLASS__ . "\n";
        return $this;
    }

    public function scan()
    {
        $cli = $this->getCli();
        $data = $this->data;

        $cli->exec("/etc/scripts/support_report.sh");
        $data['support_report'] = $cli->exec("cat /etc/config/support_report");
        $data['run'] = $cli->exec("config -g config");
        $data['version'] = $cli->exec("cat /etc/version");
        $data['serial'] = $cli->exec("showserial");

        $this->data = $data;
        $this->name = $this->getName();
        $this->serial = $this->getSerial();
        $this->model = $this->getModel();
        $this->save();
    }

    public function getName()
    {
        $reg = "/config.system.name (\S+)/";
        if(preg_match($reg,$this->data['run'], $hits))
        {
            return $hits[1];
        }
    }

    public function getSerial()
    {
        return $this->data['serial'];
    }

    public function getModel()
    {
        $reg = "/<model>(\S+)<\/model>/";
        if(preg_match($reg,$this->data['support_report'], $hits))
        {
            return $hits[1];
        }
    }


}
