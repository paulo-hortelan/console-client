<?php

namespace Meklis\Network\Console;

use Meklis\Network\Console\Helpers\DefaultHelperInterface;

class SSH extends AbstractConsole
{
    protected $session = null;

    protected $connection = null;

    public function __construct($timeout = 10, $stream_timeout = 1.0)
    {
        $this->enableCatchErrors();
        parent::__construct($timeout, $stream_timeout);
    }

    public function connect($host, $port = 22, ?DefaultHelperInterface $helper = null)
    {
        if ($helper) {
            $helper->setConnectionType('ssh');
            $this->helper = $helper;
        }

        $this->host = $host;
        $this->port = $port;
        // check if we need to convert host to IP
        if (! preg_match('/([0-9]{1,3}\\.){3,3}[0-9]{1,3}/', $this->host)) {
            $ip = gethostbyname($this->host);

            if ($this->host == $ip) {
                throw new \Exception("Cannot resolve $this->host");
            } else {
                $this->host = $ip;
            }
        }
        $originalConnectionTimeout = ini_get('default_socket_timeout');
        ini_set('default_socket_timeout', 5);
        $methods = [
            'hostkey' => 'ecdsa-sha2-nistp256,ssh-rsa',
        ];

        $ssh = ssh2_connect($this->host, $this->port, $methods);
        if (! $ssh) {
            throw new \Exception('Error connect');
        }

        ini_set('default_socket_timeout', $originalConnectionTimeout);
        if ($this->helper->getEol()) {
            $this->eol = $this->helper->getEol();
        }
        if ($this->helper->getPrompt()) {
            $this->prompt = $this->helper->getPrompt();
        }
        if ($this->helper->isEnableMagicControl()) {
            $this->enableMagicControl = $this->helper->isEnableMagicControl();
        }

        $this->connection = $ssh;

        return $this;
    }

    public function login($username, $password)
    {
        if ($sizes = $this->helper->getWindowSize()) {
            $wide = $sizes[0];
            $high = $sizes[1];
            $sizeType = SSH2_TERM_UNIT_CHARS;
        } else {
            $wide = 0;
            $high = 0;
            $sizeType = 0;
        }

        if (! ssh2_auth_password($this->connection, $username, $password)) {
            throw new \Exception('Error auth');
        }
        $this->session = ssh2_shell($this->connection, '', null, $wide, $high, $sizeType);

        try {
            $this->waitPrompt();
            if ($this->helper->isDoubleLoginPrompt()) {
                try {
                    $this->waitPrompt('', 0.2);
                } catch (\Exception $e) {
                }
            }
        } catch (\Exception $e) {
            throw new \Exception("Login failed. ({$e->getMessage()})");
        }

        return $this->runAfterLoginCommands($password);
    }

    /**
     * @return $this
     */
    public function setDeviceHelper(DefaultHelperInterface $helper)
    {
        $this->helper = $helper;
        if ($this->helper->getEol()) {
            $this->eol = $this->helper->getEol();
        }
        if ($this->helper->getPrompt()) {
            $this->prompt = $this->helper->getPrompt();
        }
        $this->enableMagicControl = $this->helper->isEnableMagicControl();
        $this->helper->setConnectionType('ssh');

        return $this;
    }

    /**
     * Closes IP socket
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function disconnect()
    {
        if ($this->session) {
            $this->runBeforeLogountCommands();
            if (! fclose($this->session)) {
                throw new \Exception('Error while closing telnet socket');
            }
            $this->session = null;
        }
        if ($this->connection) {
            ssh2_disconnect($this->connection);
        }

        return $this;
    }

    /**
     * Change terminal window size
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setWindowSize($wide = 80, $high = 40)
    {
        fwrite($this->session, $this->IAC.$this->WILL.$this->NAWS);
        $c = $this->getc();
        if ($c != $this->IAC) {
            throw new \Exception('Error: unknown control character '.ord($c));
        }
        $c = $this->getc();
        if ($c == $this->DONT || $c == $this->WONT) {
            throw new \Exception('Error: server refuses to use NAWS');
        } elseif ($c != $this->DO && $c != $this->WILL) {
            throw new \Exception('Error: unknown control character '.ord($c));
        }
        fwrite($this->session, $this->IAC.$this->SB.$this->NAWS. 0 .$wide. 0 .$high.$this->IAC.$this->SE);

        return $this;
    }

    /**
     * Gets character from the socket
     *
     * @return string $c character string
     */
    protected function getc($timeoutSec = null)
    {
        if ($timeoutSec) {
            $ts = $timeoutSec;
        } else {
            $ts = $this->stream_timeout_sec;
        }

        $until_t = time() + $ts;
        stream_set_timeout($this->session, $ts);
        TRY_READ:
        $c = fread($this->session, 1024);
        if (! $c && time() <= $until_t) {
            usleep(100);
            goto TRY_READ;
        }
        $this->global_buffer->fwrite($c);

        return $c;
    }

    /**
     * @param  $timout
     * @return $this|null
     *
     * @throws \Exception
     */
    protected function readTo($prompt, $timeout = null)
    {
        if (! $this->session) {
            throw new \Exception('SSH connection closed');
        }

        // clear the buffer
        $this->clearBuffer();

        $until_t = time() + $this->timeout;

        if ($this->helper->getWaitingResponseTimeout()) {
            $timeout = $this->helper->getWaitingResponseTimeout();
            $prompt = '';
        }

        do {
            // time's up (loop can be exited at end or through continue!)
            if (time() > $until_t) {
                throw new \Exception("Couldn't find the requested : '$prompt' within {$this->timeout} seconds");
            }

            $c = $this->getc($timeout);
            if ($c === false) {
                if (empty($prompt) && ! $this->detectPagination()) {
                    return $this;
                }
            }

            // Interpreted As Command
            if ($c == $this->IAC) {
                if ($this->negotiateSSHOptions()) {
                    continue;
                }
            }

            // append current char to global buffer
            $this->buffer .= $c;

            $latestBytes = $this->removeNotASCIISymbols(substr($this->buffer, -70));
            if ($this->detectPagination()) {
                continue;
            }
            // we've encountered the prompt. Break out of the loop
            if (! empty($prompt) && preg_match("/{$prompt}/m", trim($latestBytes))) {
                return $this;
            }
        } while ($c != $this->NULL || $c != $this->DC1);

        return null;
    }

    public function detectPagination()
    {
        $latestBytes = $this->removeNotASCIISymbols(substr($this->buffer, -70));
        if ($this->helper->getPaginationDetect()) {
            if (preg_match($this->helper->getPaginationDetect(), $latestBytes)) {
                if (! fwrite($this->session, "\n") < 0) {
                    throw new \Exception('Error writing to session');
                }
                $this->buffer = preg_replace($this->helper->getPaginationDetect(), "\n", $this->buffer);

                return true;
            }
        }

        return false;
    }

    /**
     * Write to console
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function write($buffer, $add_newline = true)
    {
        if ($this->session === null) {
            throw new \Exception("Meklis\Network\Console\SSH connection closed! Check you call method connect() before any calling");
        }
        // clear buffer from last command
        $this->clearBuffer();

        if ($add_newline == true) {
            $buffer .= $this->eol;
        }

        try {
            $this->global_buffer->fwrite($buffer);
        } catch (\Throwable $e) {
        }

        if (! fwrite($this->session, $buffer) < 0) {
            throw new \Exception('Error writing to session');
        }

        return $this;
    }

    protected function negotiateSSHOptions()
    {
        if (! $this->enableMagicControl) {
            return $this;
        }

        $c = $this->getc();
        if ($c != $this->IAC) {
            if (($c == $this->DO) || ($c == $this->DONT)) {
                $opt = $this->getc();
                fwrite($this->session, $this->IAC.$this->WONT.$opt);
            } elseif (($c == $this->WILL) || ($c == $this->WONT)) {
                $opt = $this->getc();
                fwrite($this->session, $this->IAC.$this->DONT.$opt);
            } else {
                throw new \Exception('Error: unknown control character '.ord($c));
            }
        } else {
            throw new \Exception('Error: Something Wicked Happened');
        }

        return $this;
    }

    public function enableCatchErrors()
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
    }
}
