<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Metaclassing\SSH;
use Nanigans\SingleTableInheritance\SingleTableInheritanceTrait;


class DeviceCisco extends Device
{
    use SoftDeletes;
    use SingleTableInheritanceTrait;

    protected static $singleTableSubclasses = [DeviceCisco::class];
    protected static $singleTableType = 'DeviceCisco';

    protected $fillable = [
        'type',
        'ip',
        'name', 
        'data',
        'vendor',
        'model',
        'serial',
      ];

    protected $casts = [
        'data' => 'array'
   ];

    public $username = "";
    public $password = "";

    public function discover()
    {
        $match = [
            'device.cisco'     =>  0,
            'device.aruba'     =>  0,
            'device.opengear'  =>  0,
            'device.ubiquiti'  =>  0,
        ];

        $cli = $this->getCli();
        
        $commands = [
            'ver'   =>  $cli->exec('sh ver'),
            'inv'   =>  $cli->exec('show inventory'),
        ];

        $regex = [
            'device.cisco'  =>  '/cisco/i',
            'device.aruba'  =>  '/aruba/i',
        ];

        foreach($commands as $command)
        {
            foreach($regex as $type => $reg)
            {
                if(preg_match($reg,$command))
                {
                    $match[$type]++;
                }
            }
        }
        print_r($match);
        $highcount = 0;
        $newtype = "";
        foreach($match as $type => $count)
        {
            if($count > $highcount)
            {
                $newtype = $type;
            }
        }

        $this['data->newtype'] = $newtype;

        $this->save();
    }

    public function deleteMe()
    {

    }

    public function scan()
    {

    }

    public function getCli($ip = null, $username = null, $password = null)
    {
        if(!$ip)
        {
            $ip = $this->ip;
        }
        if(!$username)
        {
            if($this->username == "")
            {
                $username = env('DEFAULT_USERNAME');
            } else {
                $username = $this->username;
            }
        }
        if(!$password)
        {
            if($this->password == "")
            {
                $password = env('DEFAULT_PASSWORD');
            } else {
                $password = $this->password;
            }
        }
        $deviceinfo = [
            'host'      =>  $ip,
            'username'  =>  $username,
            'password'  =>  $password,
        ];
        // Create a ssh object with our device information
        $cli = new SSH($deviceinfo);
        $cli->connect();
        // send the term len 0 command to stop paging output with ---more---
        $cli->exec('terminal length 0');
        $cli->exec('no paging');
        return $cli;
    }
    
}
