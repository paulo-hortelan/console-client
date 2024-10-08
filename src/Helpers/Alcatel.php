<?php

namespace Meklis\Network\Console\Helpers;

class Alcatel extends DefaultHelper
{
    protected $prompt = '[>#]';

    protected $userPrompt = '.*(ame|login):';

    protected $passwordPrompt = 'ord:';

    protected $afterLoginCommands = [
        'terminal datadump',
    ];

    protected $beforeLogoutCommands = [];

    protected $eol = "\r\n";

    protected $doubleLoginPrompt = false;

    protected $enableMagicControl = false;

    protected $paginationDetect = '/More/';

    protected $windowSize = null;
}
