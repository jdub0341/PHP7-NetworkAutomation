<?php

namespace App\Console\Commands;

use App\Jobs;
use App\Device\Device;
use App\Jobs\DiscoverDeviceJob;
use Illuminate\Console\Command;

class DiscoverDevice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'netman:discoverDevice {--ip=} {--username=} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Device Discover device by ip address. Username and password can be passed in optional';

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
        //$arguments = $this->arguments();
        //$options = $this->options();
        $ip = $this->options('ip');
        if(!$ip)
        {
            throw new \Exception('No IP specified!');
        }

        $device = new Device(['ip' => $ip]);
        \Log::info('DiscoverDeviceCommand', ['DiscoverDeviceJob' => 'starting', 'device_ip' => $device->ip]);   // Log device to the log file.
        $result = DiscoverDeviceJob::dispatch($device);		// Create a scan job for each device in the database
    }

/*
    // Create a job in the queue for the device arguments.
    public function discoverDeviceJob($id)
    {
        \Log::info('DiscoverDeviceCommand', ['DiscoverDeviceJob' => 'starting', 'device_id' => $id]);   // Log device to the log file.
        $result = DiscoverDeviceJob::dispatch($id)->onQueue('default');		// Create a scan job for each device in the database
    }
/**/

/*
    // Check if ip currently exists in the database.
    public function check_if_ip_exists_in_db($ip)
    {
        return $devices = Device::where('ip', $ip)->count();
    }
/**/

 /*
    // Seed IPs in to discover by putting them into the getIPs function.
    public function seedIPs($arguments)
    {
        $ips = $this->getIPs();

        foreach ($ips as $ip) {
            if ($this->check_if_ip_exists_in_db($ip)) {
                echo "Device with {$ip} already exists in DB.\n";
                continue;
            }

            $arguments['ip'] = $ip;

            $device = Device::create($arguments);
            $this->discoverDeviceJob($device->id);
        }
    }
    /**/

    // Can place IPs in array to create a discover job for each.
    public function getIPs()
    {
        return  [
                ];
    }
}
