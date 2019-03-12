<?php

namespace App\Console\Commands;

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
    protected $description = 'Device Discover device by ip address. Username and password can be passed in optoinal';

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
        $device = new \App\Device\Device;
		
		$device->ip = $this->argument('ipaddress');
		
		$arguments = $this->arguments(); 
		//print_r($arguments); 
		
		if(isset($arguments['username'])){
			$device->username = $this->argument('username');
		}
		if(isset($arguments['password'])){
			$device->password = $this->argument('password');
		}
		
		$result = $device->discover();
		
		print_r($result); 
		//$device = \App\Device\Device::where("ip", $device->ip)->first(); 
		//print_r($device); 
    }
}
