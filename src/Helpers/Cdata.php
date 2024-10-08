<?php

namespace Meklis\Network\Console\Helpers;

class Cdata extends DefaultHelper
{
    protected $prompt = '^(.*?More.*|[ ]{1,3})?([A-z_0-9]{1,3}.*).*[>#]$';

    protected $userPrompt = 'ame:';

    protected $passwordPrompt = 'assword:';

    protected $afterLoginCommands = [];

    protected $enableMagicControl = false;

    protected $eol = "\n";

    protected $beforeLogoutCommands = [];

    protected $paginationDetect = '/--More.*--/';

    public function isDoubleLoginPrompt(): bool
    {
        if ($this->connectionType === 'ssh') {
            return true;
        }

        return $this->doubleLoginPrompt;
    }
}
