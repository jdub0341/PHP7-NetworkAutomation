<?php

namespace App\Device\Opengear;

use phpseclib\Net\SSH2;

class Opengear extends \App\Device\Device
{ 
    protected static $singleTableSubclasses = [
    ];

    protected static $singleTableType = __CLASS__;

    public function discover()
    {
        print __CLASS__ . "\n";

/*         foreach(self::$singleTableSubclasses as $class)
        {
            $match[$class] = 0;
        }  */

        $match = [

        ];

        $regex = [

        ];

        $cli = $this->getCli();

        $commands = [

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

        return $this->convertType($newtype)->discover();
    }

    public function getCli($ip = null, $username = null, $password = null)
    {
        if(!$ip)
        {
            $ip2 = $this->ip;
        }
        if(!$username)
        {
            if($this->username == "")
            {
                $username2 = env('DEFAULT_USERNAME');
            } else {
                $username2 = $this->username;
            }
        }
        if(!$password)
        {
            if($this->password == "")
            {
                $password2 = env('DEFAULT_PASSWORD');
            } else {
                $password2 = $this->password;
            }
        }
        $deviceinfo = [
            'host'      =>  $ip2,
            'username'  =>  $username2,
            'password'  =>  $password2,
        ];
        print_r($deviceinfo);
        // Create a ssh object with our device information
        $ssh = new SSH2($deviceinfo['host']);
        if (!$ssh->login($deviceinfo['username'], $deviceinfo['password'])) {
            print "LOGIN FAILED!\n";
        }

        // send the term len 0 command to stop paging output with ---more---
        $return = $ssh->exec('cat /etc/config/support_report');
        print $return;
        //return $ssh;
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
        $this->save();
    }

}
