<?php

namespace Meklis\Network\Console\Helpers;

class Datacom extends DefaultHelper
{
    protected $prompt = '[#]';

    protected $userPrompt = 'login:';

    protected $passwordPrompt = 'Password:';

    protected $eol = "\n";

    protected $doubleLoginPrompt = false;

    protected $enableMagicControl = false;

    protected $stripPrompt = true;

    protected $windowSize = null;
}
