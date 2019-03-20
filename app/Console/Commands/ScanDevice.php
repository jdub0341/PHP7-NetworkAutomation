<?php

namespace App\Console\Commands;

use \App\Jobs; 
use \App\Jobs\ScanDeviceJob; 
use \App\Device\Device; 
use Illuminate\Console\Command;

class ScanDevice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'netman:scanDevice {id?} {--username=} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan Device by ID';

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

		
		if($arguments['id']){
			$this->scanDeviceManually($arguments); 
			//$this->scanDeviceJob($arguments);
		}
		else{
			$this->scanDeviceJobs(); 
		}
		
    
	}
	
	public function scanDeviceJobs()
	{
		// Run Scan all devices in the Database. 
		$devices = Device::all();
		
		foreach($devices as $device){
			$result = ScanDeviceJob::dispatch($device['id'])->onQueue('default');		// Create a scan job for each device in the database
		}
	}
	
	public function scanDeviceJob($arguments)
	{
		// Create a scan job for the device you enter arguments for. 
		if(!$this->check_if_id_exists_in_db($arguments['id'])){
			echo "Device ID does not exist in DB.\n"; 
			die();
		}
		
		$device = Device::find($arguments['id']);

		if(isset($arguments['username'])){
			$device->username = $this->argument('username');
		}
		if(isset($arguments['password'])){
			$device->password = $this->argument('password');
		}
		
		$result = ScanDeviceJob::dispatch($device['id']); 
		//$result = ScanDeviceJob::dispatch($device); 

	}
	
	public function scanDeviceManually($arguments)
	{
		print_r($arguments); 
		if(!$this->check_if_id_exists_in_db($arguments['id'])){
			echo "Device ID does not exist in DB.\n"; 
			die();
		}
		
		$device = Device::find($arguments['id']);

		if(isset($arguments['username'])){
			$device->username = $this->argument('username');
		}
		if(isset($arguments['password'])){
			$device->password = $this->argument('password');
		}

		$result = $device->scan();

		print_r(json_decode(json_encode($result), true)); 
	}
	
	public function check_if_id_exists_in_db($id)
	{
		return $devices = Device::where('id', $id)->count(); 
	}
}
