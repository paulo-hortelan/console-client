<?php

namespace Meklis\Network\Console\Helpers;

class Fiberhome extends DefaultHelper
{
    protected $prompt = '[;]';

    protected $loginCommands = [['command' => 'LOGIN:::CTAG::UN={username},PWD={password};', 'prompt' => '[;]']];

    protected $eol = "\n";

    protected $afterLoginCommands = [
        'INH-MSG-ALL::ALL:::;',
    ];

    protected $doubleLoginPrompt = false;

    protected $enableMagicControl = false;

    protected $stripPrompt = true;
}
