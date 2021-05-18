<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class processTcpdump extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'netman:processTcpdump';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process TCPDUMP packets for autodiscovery';

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
        while ( !feof(STDIN) )                  // Loop while there is a STDIN stream
        {
            $LINE = fgets(STDIN, 8192);     // Get a line of input to process
            $REGEX = "/^(\S+)\s+IP\s+(\d+\.\d+\.\d+\.\d+)\.(\d+)\s>\s(\d+\.\d+\.\d+\.\d+)\.(\d+):\s(.+)$/";
            if ( preg_match($REGEX,$LINE,$MATCH) )
            {
                    // process the output:
                    //$TIME         = $MATCH[1];    // Who cares?
                    $SRC_IP         = $MATCH[2];
                    //$SRC_PORT     = $MATCH[3];    // Who cares?
                    $DST_IP         = $MATCH[4];
                    $DST_PORT       = $MATCH[5];
                    //$EXTRA        = $MATCH[6];    // Who cares?
                echo $SRC_IP;
                $this->call('netman:AutoDiscoverDevice',['--ip' => $SRC_IP]);
            }
        }
    }
}
