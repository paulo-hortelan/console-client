<?php

namespace Meklis\Network\Console\Helpers;

class Nokia extends DefaultHelper
{
    protected $prompt = '[#]';

    protected $userPrompt = 'login:';

    protected $passwordPrompt = 'password:';

    protected $afterLoginCommands = [
        'environment inhibit-alarms',
    ];

    protected $eol = "\n";

    protected $doubleLoginPrompt = false;

    protected $enableMagicControl = false;

    protected $stripPrompt = true;
}
