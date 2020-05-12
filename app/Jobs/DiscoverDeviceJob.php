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

    public $ip;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ip)
    {
        $this->ip = $ip;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!$this->ip)
        {
            throw new \Exception('No IP specified!');
        }
        \Log::info(__FILE__, ['function' => __FUNCTION__, 'state' => 'starting', 'ip' => $this->ip]);   // Log device to the log file.
        $device = new Device(['ip' => $this->ip]);
        $device->discover();
        \Log::info(__FILE__, ['function' => __FUNCTION__, 'state' => 'complete', 'ip' => $this->ip]);   // Log device to the log file.
    }
}
