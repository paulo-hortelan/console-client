<?php

namespace Meklis\Network\Console\Helpers;

class ZTEC300 extends DefaultHelper
{
    protected $prompt = '[>#]';
    protected $userPrompt = 'Username:';
    protected $passwordPrompt = 'Password:';
    protected $afterLoginCommands = [
        'terminal length 0'
    ];
    protected $eol = "\n";
    protected $doubleLoginPrompt = false;
    protected $enableMagicControl = false;
    protected $stripPrompt = true;
}
