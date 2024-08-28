<?php

namespace Meklis\Network\Console\Helpers;

class Digistar extends DefaultHelper
{
    protected $prompt = '[>]';

    protected $passwordPrompt = 'Password:';

    protected $afterLoginCommands = [
        'terminal length 0',
    ];

    protected $eol = "\n";

    protected $doubleLoginPrompt = false;

    protected $enableMagicControl = false;

    protected $stripPrompt = true;
}
