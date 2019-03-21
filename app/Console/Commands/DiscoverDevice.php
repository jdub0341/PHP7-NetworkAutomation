<?php

namespace App\Console\Commands;

use \App\Jobs; 
use \App\Jobs\DiscoverDeviceJob; 
use \App\Device\Device; 
use Illuminate\Console\Command;

class DiscoverDevice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'netman:discoverDevice {ipaddress?} {--username=} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Device Discover device by ip address. Username and password can be passed in optional';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		$arguments = $this->arguments();
		
		if($arguments['ipaddress']){
			
			
			if($this->check_if_ip_exists_in_db($arguments['ipaddress'])){		// Check if IP already exists in the devices table. 
				throw new \Exception("IP already exists.");
			}
			
			//$this->discoverDeviceManual($arguments); // Uncomment if you want to manually scan IP. 
			$this->discoverDeviceJob($arguments);
		}
		else{
			// Check seedIPs if no IP is provided.
			if(!$this->getIPs()){
				throw new \Exception("No IPs provided to scan."); 
			}
			
			$this->seedIPs($arguments); 
		}
    }
	
	// Create a job in the queue for the device arguments. 
	public function discoverDeviceJob($arguments)
    {
		\Log::info('DiscoverDeviceCommand', ['DiscoverDeviceJob' => 'starting', 'device_ip' => $arguments['ipaddress']]);   // Log device to the log file.
		$result = DiscoverDeviceJob::dispatch($arguments)->onQueue('default');		// Create a scan job for each device in the database
    }
	
	// Manually Discover Device without creating a queued job for it. 
    public function discoverDeviceManual($arguments)
    {
		if($this->check_if_ip_exists_in_db($arguments['ipaddress'])){
			throw new \Exception("IP already exists.");
		}
		
		$device = new Device; 
		
		$device->ip = $arguments['ipaddress']; 
		if(isset($arguments['username'])){
			$device->username = $this->argument('username');
		}
		if(isset($arguments['password'])){
			$device->password = $this->argument('password');
		}
		
		$result = $device->discover();
    }
	
	// Check if ip currently exists in the database. 
	public function check_if_ip_exists_in_db($ipaddress)
    {
		return $devices = Device::where('ip', $ipaddress)->count(); 
	}
	
	// Seed IPs in to discover by putting them into the getIPs function. 
	public function seedIPs($arguments)
    {
		$ips = $this->getIPs(); 
		
		foreach($ips as $ip){
			$arguments['ipaddress'] = $ip; 
			
			if($this->check_if_ip_exists_in_db($arguments['ipaddress'])){
				echo "Device already exists in DB.\n"; 
				continue;
			}

			$this->discoverDeviceJob($arguments);
		}
    }
	
	// Can place IPs in array to create a discover job for each. 
	public function getIPs()
    {
		return  []; 
	}
}
