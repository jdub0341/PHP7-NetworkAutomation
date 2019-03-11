<?php

namespace App\Device;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Metaclassing\SSH;
use phpseclib\Net\SSH2;
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

    public function generateDeviceInfo()
    {
        if($this->username == "")
        {
            $username = env('DEFAULT_USERNAME');
        } else {
            $username = $this->username;
        }
        if($this->password == "")
        {
            $password = env('DEFAULT_PASSWORD');
        } else {
            $password = $this->password;
        }

        $deviceinfo = [
            'host'      =>  $this->ip,
            'username'  =>  $username,
            'password'  =>  $password,
        ];
        return $deviceinfo;
    }

    public function getCli()
    {
        $deviceinfo = $this->generateDeviceInfo();
        // Create a ssh object with our device information
        try{
            $cli = new SSH($deviceinfo);
            $cli->connect();
        } catch (\Exception $e) {
            $cli = new SSH2($deviceinfo['host']);
            if (!$cli->login($deviceinfo['username'], $deviceinfo['password']))
            {
                exit('Login Failed');
            }
        }

        // send the term len 0 command to stop paging output with ---more---
        $cli->exec('terminal length 0');
        $cli->exec('no paging');
        return $cli;
    }

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
            'cat /etc/version',
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

        $cli->disconnect();

        return $this->convertType($newtype)->discover();
    }

    public function scan()
    {

    }

    public function getName()
    {

    }

    public function getSerial()
    {

    }

    public function getModel()
    {

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
