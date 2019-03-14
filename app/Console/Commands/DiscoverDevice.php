<?php

namespace App\Console\Commands;

use \App\Device\Device; 
use Illuminate\Console\Command;

class DiscoverDevice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'netman:discoverDevice {ipaddress} {--username=} {--password=}';

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

		if($this->check_if_ip_exists_in_db($arguments['ipaddress'])){
			echo "Device already exists in DB.\n"; 
			die();
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
		
		//print_r(json_decode(json_encode($result), true));
		
    }
	
	public function check_if_ip_exists_in_db($ipaddress)
    {
		return $devices = Device::where('ip', $ipaddress)->count(); 
	}
}
