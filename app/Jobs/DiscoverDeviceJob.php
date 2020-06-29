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

    public $options;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->options['id'])
        {
            $device = Device::findOrFail($this->options['id']);
        }
        if($this->options['ip'])
        {
            $device = new Device(['ip' => $this->options['ip']]);
        }
        \Log::info(__FILE__, ['function' => __FUNCTION__, 'state' => 'starting', 'ip' => $device->ip]);   // Log device to the log file.
        $device->discover();
        \Log::info(__FILE__, ['function' => __FUNCTION__, 'state' => 'complete', 'ip' => $device->ip]);   // Log device to the log file.
    }
}
