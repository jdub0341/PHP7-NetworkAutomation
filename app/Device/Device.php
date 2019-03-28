<?php

namespace App\Device;

use DB;
use Metaclassing\SSH;
use phpseclib\Net\SSH2;
use App\Credential\Credential;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nanigans\SingleTableInheritance\SingleTableInheritanceTrait;

class Device extends Model
{
	use Searchable;	// Add for Scout to search
    use SoftDeletes;
    use SingleTableInheritanceTrait;
	
	// Scout Searchable
	public function toSearchableArray()
    {
        $array = $this->toArray();
		$array['data'] = json_encode($array['data'], true); // Change data to json encoded for Scout tnt driver to search. Cannot do nested array search. 
		return $array;
    }

    protected $table = 'devices';
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
        'data' => 'array',
    ];

    public function credential()
    {
        return $this->hasOne('App\Credential\Credential', 'id', 'credential_id');
    }

    public $cmds = [];

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
                $this->save();

                return $cli;
            }
        }
    }

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

    public static function getSSH2($ip, $username, $password)
    {
        //Try using phpseclib\Net\SSH2 to connect to device.
        $cli = new SSH2($ip);
        if ($cli->login($username, $password)) {
            return $cli;
        }
    }

    /*
    This method is used to determine the TYPE of device this is and recategorize it.
    Once recategorized, it will perform discover() again.
    Returns null;
    */
    public function discover()
    {
        echo __CLASS__."\n";
        $this->save();
        /*
        This goes through each SUBCLASS defined above and builds (2) arrays:
        $match = an array of classes and how many MATCHES we have (starts at 0 for each)
        $regex = an array of regex to be used for matching.
        */

        foreach (self::$singleTableSubclasses as $class) {
            $match[$class] = 0;
            $tmp = explode('\\', $class);
            $regex[$class] = '/'.end($tmp).'/i';
        }

        $cli = $this->getCli();

        //Commands to be run on this unknown device to help us determine WHAT it is.
        $commands = [
            'sh ver',
            'show inventory',
            'cat /etc/version',
        ];

        /*
        Go through each COMMAND and execute it. and see if it matches each of the $regex entries we have.
        If we find a match, +1 for that class.
        */
        foreach ($commands as $command) {
            $output = $cli->exec($command);
            foreach ($regex as $class => $reg) {
                if (preg_match($reg, $output)) {
                    $match[$class]++;
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
    This method is used to SCAN the device to obtain all of the command line outputs that we care about.
    This also configures database indexes for NAME, SERIAL, and MODEL.
    returns null
    */
    public function scan()
    {
        $cli = $this->getCli();
        //Grab a copy of our existing data.
        $data = $this->data;

        //Loop through each configured command and save it's output to $data.
        foreach ($this->cmds as $key => $cmd) {
            $data[$key] = $cli->exec($cmd);
        }
        //save the data back to the model.
        $this->data = $data;
        //set indexes for NAME, SERIAL, and MODEL
        $this->name = $this->getName();
        $this->serial = $this->getSerial();
        $this->model = $this->getModel();

        $this->save();
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

    /*     public function save($options = [])
        {
            $devices = Device::where("ip",$this->ip)->get();
            if($devices->count() == 0)
            {
                return parent::save($options);
            } else {
                throw new \Exception("Device with IP " . $this->ip . " already exists.");
            }
        } */
}
