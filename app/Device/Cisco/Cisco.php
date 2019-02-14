<?php

namespace App\Device\Cisco;

class Cisco extends \App\Device\Device
{ 
    protected static $singleTableSubclasses = [
        IOS::class,
        IOSXE::class,
        IOSXR::class,
        NXOS::class,
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
        $this->save();
    }

}
