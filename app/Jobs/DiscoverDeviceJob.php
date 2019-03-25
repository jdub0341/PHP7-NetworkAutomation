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
	
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
		$this->device = Device::findOrFail($id); 
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {	
		\Log::info('DiscoverDeviceJob', ['DiscoverDeviceJob' => 'starting', 'device_id' => $this->device->id]);   // Log device to the log file. 
		$this->device->discover();
		\Log::info('DiscoverDeviceJob', ['DiscoverDeviceJob' => 'complete', 'device_id' => $this->device->id]);   // Log device to the log file. 
    }

}
