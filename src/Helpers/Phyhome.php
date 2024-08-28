<?php

namespace Meklis\Network\Console\Helpers;

class Phyhome extends DefaultHelper
{
    protected $prompt = '[>]';

    protected $userPrompt = ':';

    protected $passwordPrompt = ':';

    protected $eol = "\n";

    protected $doubleLoginPrompt = false;

    protected $enableMagicControl = false;

    protected $stripPrompt = true;
}
