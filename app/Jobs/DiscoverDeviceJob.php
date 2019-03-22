<?php

namespace App\Jobs;

use \App\Device\Device; 
use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DiscoverDeviceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $device; 
	public $ip; 
	
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($arguments)
    {
		$this->ip = $arguments['ipaddress'];
		
		$this->device = new Device; 
		
		$this->device->ip = $arguments['ipaddress']; 
		
		if(isset($arguments['username'])){
			$this->device->username = $this->argument('username');
		}
		if(isset($arguments['password'])){
			$this->device->password = $this->argument('password');
		}
		
		$this->device->save();		// Had to save this for some reason to all the handle. Kept saying 1 argument needed, 0 provided. 
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {	
		\Log::info('DiscoverDeviceJob', ['DiscoverDeviceJob' => 'starting', 'device_ip' => $this->ip]);   // Log device to the log file. 
		$this->device->discover();
		\Log::info('DiscoverDeviceJob', ['DiscoverDeviceJob' => 'complete', 'device_ip' => $this->ip]);   // Log device to the log file. 
    }

}
