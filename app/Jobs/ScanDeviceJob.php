<?php

namespace App\Jobs;

use App\Device\Device;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ScanDeviceJob implements ShouldQueue
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
		\Log::info(__FILE__, ['function' => __FUNCTION__, 'state' => 'starting', 'device_id' => $this->device->id]);   // Log device to the log file. 
		$this->device->discover();
		\Log::info(__FILE__, ['function' => __FUNCTION__, 'state' => 'complete', 'device_id' => $this->device->id]);   // Log device to the log file. 
    }
}
