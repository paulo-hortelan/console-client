<?php

namespace Meklis\Network\Console\Helpers;

class DefaultHelper implements DefaultHelperInterface
{
    protected $prompt = '[%>#$]';

    protected $userPrompt = 'ame:';

    protected $passwordPrompt = 'ord:';

    protected $loginCommands = [];

    protected $stripPrompt = true;

    protected $afterLoginCommands = [];

    protected $beforeLogoutCommands = [];

    protected $doubleLoginPrompt = false;

    protected $enableMagicControl = false;

    protected $eol = "\n";

    protected $connectionType;

    protected $windowSize = null;

    protected $paginationDetect = '';

    protected $ignoreEOF = false;

    protected $waitingResponseTimeout = null;

    public function getWaitingResponseTimeout()
    {
        return $this->waitingResponseTimeout;
    }

    public function setWaitingResponseTimeout($waitingResponseTimeout): DefaultHelper
    {
        $this->waitingResponseTimeout = $waitingResponseTimeout;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrompt()
    {
        return $this->prompt;
    }

    public function isStripPrompt(): bool
    {
        return $this->stripPrompt;
    }

    public function setStripPrompt(bool $stripPrompt): DefaultHelper
    {
        $this->stripPrompt = $stripPrompt;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserPrompt()
    {
        return $this->userPrompt;
    }

    /**
     * @return string
     */
    public function getPasswordPrompt()
    {
        return $this->passwordPrompt;
    }

    /**
     * @return array
     */
    public function getAfterLoginCommands()
    {
        return $this->afterLoginCommands;
    }

    /**
     * @return array
     */
    public function getLoginCommands(?string $username = null, ?string $password = null)
    {
        if (! empty($username) && ! empty($password)) {
            foreach ($this->loginCommands as &$loginCommand) {
                $loginCommand['command'] = str_replace(['{username}', '{password}'], [$username, $password], $loginCommand['command']);
            }
        }

        return $this->loginCommands;
    }

    /**
     * @return array
     */
    public function getBeforeLogoutCommands()
    {
        return $this->beforeLogoutCommands;
    }

    /**
     * @return bool
     */
    public function isDoubleLoginPrompt()
    {
        return $this->doubleLoginPrompt;
    }

    /**
     * @return bool
     */
    public function isEnableMagicControl()
    {
        return $this->enableMagicControl;
    }

    /**
     * @return string
     */
    public function getEol()
    {
        return $this->eol;
    }

    /**
     * @return int[] | null
     */
    public function getWindowSize()
    {
        return $this->windowSize;
    }

    /**
     * @return mixed
     */
    public function getConnectionType()
    {
        return $this->connectionType;
    }

    /**
     * @param  mixed  $connectionType
     * @return DefaultHelper
     */
    public function setConnectionType($connectionType)
    {
        $this->connectionType = $connectionType;

        return $this;
    }

    public function setAfterLoginCommands(array $afterLoginCommands): DefaultHelper
    {
        $this->afterLoginCommands = $afterLoginCommands;

        return $this;
    }

    /**
     * @param  array  $afterLoginCommands
     */
    public function addAfterLoginCommand($command, $no_wait = false, $usleep_after = 0): DefaultHelper
    {
        $this->afterLoginCommands[] = [
            'command' => $command,
            'no_wait' => $no_wait,
            'usleep' => $usleep_after,
        ];

        return $this;
    }

    public function getPaginationDetect(): string
    {
        return $this->paginationDetect;
    }

    public function setPaginationDetect(string $paginationDetect): DefaultHelper
    {
        $this->paginationDetect = $paginationDetect;

        return $this;
    }

    public function setEol($eol)
    {
        $this->eol = "\r\n";
    }

    public function isIgnoreEOF(): bool
    {
        return $this->ignoreEOF;
    }

    public function setIgnoreEOF(bool $ignoreEOF): DefaultHelper
    {
        $this->ignoreEOF = $ignoreEOF;

        return $this;
    }
}
