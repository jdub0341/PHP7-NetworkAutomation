<?php

namespace App\Console\Commands;

use App\Jobs;
use App\Device\Device;
use App\Jobs\ScanDeviceJob;
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
			//$this->scanDeviceManually($arguments); 
			$this->scanDeviceJob($arguments);
		}
		else{
			$this->scanDeviceJobs(); 
		}
		
    
	}
	
	// Run Scan all devices in the Database. 
	public function scanDeviceJobs()
	{
		//$devices = Device::all();
		
		$devices = Device::select('id')->get();
		
		foreach($devices as $device){
			\Log::info(__FILE__, ['function' => __FUNCTION__, 'state' => 'create', 'device_id' => $device['id']]);   // Log device to the log file. 
			$result = ScanDeviceJob::dispatch($device['id'])->onQueue('default');		// Create a scan job for each device in the database
		}
	}
	
	// Call this function manually by uncommenting in handle. This will queue the work. 
	public function scanDeviceJob($arguments)
	{
		// Create a scan job for the device you enter arguments for. 
		if(!$this->check_if_id_exists_in_db($arguments['id'])){
			throw new \Exception("Device ID {$arguments['id']} does not exist in DB.\n");
		}
		
		$device = Device::find($arguments['id']);

		if(isset($arguments['username'])){
			$device->username = $this->argument('username');
		}
		if(isset($arguments['password'])){
			$device->password = $this->argument('password');
		}
		
		$result = ScanDeviceJob::dispatch($device->id); 
		
	}
	
	// Call this function manually by uncommenting in handle. This will not queue the work. 
	public function scanDeviceManually($arguments)
	{
		print_r($arguments); 
		if(!$this->check_if_id_exists_in_db($arguments['id'])){
			throw new \Exception("Device ID {$arguments['id']} does not exist in DB.\n");
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
