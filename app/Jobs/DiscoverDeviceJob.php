<?php

namespace App\Jobs;

use App\Device\Device;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
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
    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!$this->device->ip)
        {
            throw new \Exception('No IP specified!');
        }
        \Log::info(__FILE__, ['function' => __FUNCTION__, 'state' => 'starting', 'ip' => $this->device->ip]);   // Log device to the log file.
        $this->device->discover();
        \Log::info(__FILE__, ['function' => __FUNCTION__, 'state' => 'complete', 'ip' => $this->device->ip]);   // Log device to the log file.
    }
}
