<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Metaclassing\SSH;
use Nanigans\SingleTableInheritance\SingleTableInheritanceTrait;


class Device extends Model
{
    use SoftDeletes;
    use SingleTableInheritanceTrait;

    protected $table = "devices";
    protected static $singleTableTypeField = 'type';
    protected static $singleTableSubclasses = [DeviceCisco::class];
    protected static $singleTableType = 'Device';
 
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
            'DeviceCisco'     =>  0,
            'DeviceAruba'     =>  0,
            'DeviceOpengear'  =>  0,
            'DeviceUbiquiti'  =>  0,
        ];

        $cli = $this->getCli();
        
        $commands = [
            'ver'   =>  $cli->exec('sh ver'),
            'inv'   =>  $cli->exec('show inventory'),
        ];

        $regex = [
            'DeviceCisco'  =>  '/cisco/i',
            'DeviceAruba'  =>  '/aruba/i',
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
