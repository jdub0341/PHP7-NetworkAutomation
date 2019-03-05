<?php

namespace App\Device;

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
    protected static $singleTableSubclasses = [
        Aruba\Aruba::class,
        Cisco\Cisco::class,
        Opengear\Opengear::class,
    ];
    protected static $singleTableType = __CLASS__;
 
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
        foreach(self::$singleTableSubclasses as $class)
        {
            $match[$class] = 0;
            $tmp = explode('\\', $class);
            $regex[$class] = "/" . end($tmp) . "/i";
        } 

        $cli = $this->getCli();
        
        $commands = [
            'sh ver',
            'show inventory',
            'cat /etc/config/support_report',
        ];

        foreach($commands as $command)
        {
            $output = $cli->exec($command);
            foreach($regex as $class => $reg)
            {
                if(preg_match($reg,$output))
                {
                    $match[$class]++;
                }
            }
        }

        arsort($match);
        $tmp = array_keys($match);
        $newtype = reset($tmp);

        return $this->convertType($newtype)->discover();
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

    public function convertType($newtype)
    {
        return unserialize(sprintf(
            'O:%d:"%s"%s',
            strlen($newtype),
            $newtype,
            strstr(strstr(serialize($this), '"'), ':')
        ));
    }
    
}
