<?php

namespace App\Device\Cisco;

class IOS extends \App\Device\Cisco\Cisco
{
    protected static $singleTableSubclasses = [
    ];

    protected static $singleTableType = __CLASS__;

    public $discover_commands = [
    ];

    public $discover_regex = [
    ];

    public $parser = "\ohtarr\Cisco\IOS\Parser";

}
