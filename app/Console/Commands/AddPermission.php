<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Bouncer;

class addPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'netman:addPermission {role} {group}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add permissions.  Roles available = ADMIN, ENGINEER, VIEWER';

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
        $role = $this->argument('role');
        $group = $this->argument('group');
        $this->assignAdminGroupBouncerRoles($role,$group);
    }

    protected function assignAdminGroupBouncerRoles($role,$group)
    {
        echo 'Starting Assigning Permissions to '.$group.PHP_EOL;
        if(strcasecmp("admin",$role) == 0)
        {
            $tasks = [
                'create',
                'read',
                'update',
                'delete',
            ];
            $types = [
                \App\Device\Device::class,
                //END-OF-PERMISSION-TYPES
            ];
        } elseif(strcasecmp("engineer",$role) == 0){
            $tasks = [
                'create',
                'read',
                'update',
                'delete',
            ];
            $types = [
                \App\Device\Device::class,
                //END-OF-PERMISSION-TYPES
            ];
        } elseif(strcasecmp("viewer",$role) == 0){
            $tasks = [
                'read',
            ];
            $types = [
                \App\Device\Device::class,
                //END-OF-PERMISSION-TYPES
            ];
        } else {
            print "Invalid ROLE...\n";
            return false;
        }
        
        foreach ($types as $type) {
            foreach ($tasks as $task) {
                Bouncer::allow($group)->to($task, $type);
            }
        }

        echo 'Finished Assigning Permissions'.PHP_EOL;
    }
}
