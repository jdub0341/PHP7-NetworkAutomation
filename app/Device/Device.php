<?php

namespace App\Device;

use DB;
use Metaclassing\SSH;
use phpseclib\Net\SSH2;
use Laravel\Scout\Searchable;
use App\Credential\Credential;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nanigans\SingleTableInheritance\SingleTableInheritanceTrait;

class Device extends Model
{
    //use Searchable;	// Add for Scout to search */
    use SoftDeletes;
    use SingleTableInheritanceTrait;

    // Scout Searchable
    /*
    public function toSearchableArray()
    {
        $array = $this->toArray();
        print_r($array);
        if(isset($array['data']))
        {
            $array['data'] = json_encode($array['data'], true); // Change data to json encoded for Scout tnt driver to search. Cannot do nested array search.
        }
        return $array;
    }*/

    protected $table = 'devices';
    protected static $singleTableTypeField = 'type';
    protected static $singleTableSubclasses = [
        \App\Device\Aruba\Aruba::class,
        \App\Device\Cisco\Cisco::class,
        \App\Device\Opengear\Opengear::class,
        \App\Device\Ubiquiti\Ubiquiti::class,
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
        'data' => 'array',
    ];

    public $scan_cmds = [];

    public $discover_commands = [
        'sh ver',
        'show inventory',
        'cat /etc/version',
        'cat /etc/board.info',
    ];

    public $discover_regex = [
        'App\Device\Aruba\Aruba'   => [
            '/Aruba/i',
        ],
        'App\Device\Cisco\Cisco'     => [
            '/Cisco/i',
        ],
        'App\Device\Opengear\Opengear'   => [
            '/Opengear/i',
        ],
        'App\Device\Ubiquiti\Ubiquiti'   => [
            '/NBE-5AC/i',
        ],
    ];

    public $parser = null;
    
    public $parsed = null;

    public function credential()
    {
        return $this->hasOne('App\Credential\Credential', 'id', 'credential_id');
    }

    /*
    This method is used to generate a COLLECTION of credentials to use to connect to this device.
    Returns a COLLECTION
    */
    public function getCredentials()
    {
        if ($this->credential) {
            //If the device already has a credential assigned for use, return it in a collection.
            return collect([$this->credential]);
        } else {
            //Find all credentials matching the CLASS of the device first.
            $classcreds = Credential::where('class', get_class($this))->get();
            //Find all credentials that are global (not class specific).
            $allcreds = Credential::whereNull('class')->get();
        }
        //Return a collection of credentials to attempt.
        return $classcreds->merge($allcreds);
    }

    /*
    This method is used to establish a CLI session with a device.
    It will attempt to use Metaclassing\SSH library to work with specific models of devices that do not support ssh2.0 natively.
    If it fails to establish a working SSH session with Metaclassing\SSH, it will then attempt using phpseclib\Net\SSH2.
    Returns a Metaclassing\SSH object OR a phpseclib\Net\SSH2 object.
    */
    public function getCli()
    {
        $cli = null;
        //Get our collection of credentials to attempt and foreach them.
        $credentials = $this->getCredentials();
        foreach ($credentials as $credential) {
            // Attempt to connect using Metaclassing\SSH library.
            try {
                $cli = $this->getSSH1($this->ip, $credential->username, $credential->passkey);
            } catch (\Exception $e) {
                echo $e->getMessage()."\n";
            }

            if (! $cli) {
                //Attemp to connect using phpseclib\Net\SSH2 library.
                try {
                    $cli = $this->getSSH2($this->ip, $credential->username, $credential->passkey);
                } catch (\Exception $e) {
                    echo $e->getMessage()."\n";
                }
            }

            if ($cli) {
                $this->credential_id = $credential->id;
                //$this->save();

                return $cli;
            }
        }
    }

    /*
    This method is used to attempt an SSH V1 terminal connection to the device.
    It will attempt to use Metaclassing\SSH library to work with specific models of devices that do not support ssh 2.0 natively.
    If it successfully connects and detects prompt, it will return a CLI handle.
    */
    public static function getSSH1($ip, $username, $password)
    {
        $deviceinfo = [
            'host'      => $ip,
            'username'  => $username,
            'password'  => $password,
        ];
        $cli = new SSH($deviceinfo);
        $cli->connect();
        if ($cli->connected) {
            // send the term len 0 command to stop paging output with ---more---
            $cli->exec('terminal length 0');  //Cisco
            $cli->exec('no paging');  //Aruba
            return $cli;
        }
    }

    /*
    This method is used to attempt an SSH V2 terminal connection to the device.
    It will utilize the phpseclib\net\SSH library and return a CLI handle if successful
    */
    public static function getSSH2($ip, $username, $password)
    {
        //Try using phpseclib\Net\SSH2 to connect to device.
        $cli = new SSH2($ip);
        if ($cli->login($username, $password)) {
            return $cli;
        }
    }

    /*
    This method is used to determine if this devices IP is already in the database.
    Returns null;
    */
    public function pre_discover()
    {
        if(!$this->ip){
            print "No IP address found!\n";
            return false;
        }
        $device = Device::where("ip",$this->ip)->first();
        if($device){
            print "DEVICE IP ALREADY EXISTS!\n";
        } else {
            $device = $this->discover();
        }
        return $device;
    }

    /*
    This method is used to determine the TYPE of device this is and recategorize it.
    Once recategorized, it will perform discover() again.
    Returns null;
    */
    public function discover()
    {
        /*
        If an ip doesn't exist on this object you are trying to discover, fail
        Check if a device with this IP already exists.  If it does, grab it from the database and perform a discovery on it
        */
        if(!$this->ip){
            print "No IP address found!\n";
            return false;
        }

        echo get_called_class()."\n";

        if(empty(static::$singleTableSubclasses))
        {
            return $this->post_discover();
        }

        /*
        This goes through each $discover_regex defined above and builds (1) array:
        $match = an array of classes and how many MATCHES we have (starts at 0 for each)
        Example:
            Array
            (
                [App\Device\Aruba\Aruba] => 0
                [App\Device\Cisco\Cisco] => 0
                [App\Device\Opengear\Opengear] => 0
            )
        */
        foreach(static::$singleTableSubclasses as $class)
        {
            $match[$class] = 0;
        }

        $cli = $this->getCli();

        /*
        Go through each COMMAND and execute it. and see if it matches each of the $regex entries we have.
        If we find a match, +1 for that class.
        */
        foreach ($this->discover_commands as $command) {
            $output = $cli->exec($command);
            foreach ($this->discover_regex as $class => $regs) {
                foreach($regs as $reg)
                {
                    if (preg_match($reg, $output)) {
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
        //Create a new model instance of type $newtype
        $device = $newtype::make($this->toArray());
        //run discover again.
        $device = $device->discover();
        return $device;
    }

    /*
    This method is used to determine if this devices IP is already in the database.
    Returns null;
    */
    public function post_discover()
    {
        $this->scan();
        $devices = Device::where('ip',$this->ip)
            ->orWhere("serial", $this->serial)
            ->orWhere("name", $this->name)
            ->get();
        if($devices->isNotEmpty())
        {
            print "Device with name, serial, or IP already exists in database!  Cancelling Add to database!\n";
            return null;
        } else {
            $this->save();
            return $this;
        }
    }

    /*
    This method is used to SCAN the device to obtain all of the command line outputs that we care about.
    This also configures database indexes for NAME, SERIAL, and MODEL.
    returns null
    */
    public function scan()
    {
        $cli = $this->getCli();
        //Loop through each configured command and save it's output to $data.
        foreach ($this->scan_cmds as $key => $cmd) {
            $data[$key] = $cli->exec($cmd);
        }
        //save the data back to the model.
        $this->data = $data;
        //set indexes for NAME, SERIAL, and MODEL
        $this->name = $this->getName();
        $this->serial = $this->getSerial();
        $this->model = $this->getModel();
        return $this;
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

    public function parse(){
        $cp = new $this->parser("");
        foreach($this->data as $key=>$value){
            $cp->input_data($value,$key);
        }
        $this->parsed = $cp->output;
        return $this->parsed;
    }

}
