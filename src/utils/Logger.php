<?php

/**
 * Simple Logger
 *
 * @author Daniel Gehn <me@theinad.com>
 * @copyright 2017-2018 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class Logger
{
    private $enabled = true;
    private $path;
    private $prefix;
    private $logToFile = true;
    private $messages = array();

    public function __construct($path, $prefix, $enabled = true, $logToFile = true)
    {
        $this->path = $path;
        $this->prefix = $prefix;
        $this->enabled = $enabled;
        $this->logToFile = $logToFile;
    }

    /**
     * Logs debug messages to the logfile, if debugging is enabled.
     *
     * @param string $message message to be logged
     */
    public function log($message)
    {
        if ($this->enabled === true) {
            $formattedMessage = $this->formatMessage($message);
            if ($this->logToFile) {
                file_put_contents($this->path, $formattedMessage . "\n", FILE_APPEND);
            }
            $this->messages[] = $formattedMessage;
        }
    }

    /**
     * Returns a formatted message including the current date and the component prefix.
     * 
     * @param string unformatted message
     * @return string formatted message
     */
    public function formatMessage($message)
    {
        return "[" . date("c") . "] [$this->prefix]: $message";
    }
}
