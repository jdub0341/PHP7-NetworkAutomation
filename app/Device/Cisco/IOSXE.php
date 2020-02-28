<?php

namespace App\Device\Cisco;

class IOSXE extends \App\Device\Cisco\Cisco
{
    protected static $singleTableSubclasses = [
    ];
    
    protected static $singleTableType = __CLASS__;
 
    public $parser = "\ohtarr\CiscoIosxeParse";
 
}
