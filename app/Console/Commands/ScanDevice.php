<?php

namespace App\Console\Commands;

use \App\Device\Device; 
use Illuminate\Console\Command;

class ScanDevice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'netman:scanDevice {id} {--username=} {--password=}';

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
		
		$result = $device->discover();

		// print_r(json_decode(json_encode($result), true)); 
    
	}
	
	public function check_if_id_exists_in_db($id)
	{
		return $devices = Device::where('id', $id)->count(); 
	}
}
