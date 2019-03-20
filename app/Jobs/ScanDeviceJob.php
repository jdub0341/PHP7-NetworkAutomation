<?php

namespace App\Jobs;

use App\Device; 
use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Queueable;
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
		$this->device = DB::table('devices')->where('id', $id)->first();		// Get the device from db to get the device type. 
		$this->device = $this->device->type::find($this->device->id);			// Get the correct model and refech the device. 
    }
	

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		$device = $this->device->scan();  						// Scan the device and resave to the DB. 
		\Log::info('ScanDeviceJob', ['data' => $device]);		// Log device to the log file. 
    }
}
