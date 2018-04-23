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
    private $className;
    private $logToFile = true;
    private $events = array();

    public function __construct($path, $className, $enabled = true, $logToFile = true)
    {
        $this->path = $path;
        $this->className = $className;
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
            $this->events[] = [
                'logger' => $this->className,
                'timestamp' => time(),
                'formattedDate' => $this->getFormattedDate(),
                'message' => $message,
            ];
        }
    }

    /**
     * Returns a formatted message including the current date and the component className.
     *
     * @param string unformatted message
     * @return string formatted message
     */
    public function formatMessage($message)
    {
        return "[" . $this->getFormattedDate() . "] [$this->className]: $message";
    }

    protected function getFormattedDate()
    {
        return date("c");
    }

    /**
     * Dumps all logged events.
     *
     * @return void
     */
    public function dump()
    {
        var_dump($this->events);
    }

    /**
     * Returns all logged events.
     *
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }
}
