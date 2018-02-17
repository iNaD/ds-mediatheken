<?php

/**
 * Simple Logger
 *
 * @author Daniel Gehn <me@theinad.com>
 * @copyright 2017 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class Logger
{
    private $enabled = true;
    private $path;
    private $prefix;

    public function __construct($path, $prefix, $enabled = true)
    {
        $this->path = $path;
        $this->prefix = $prefix;
        $this->enabled = $enabled;
    }

  /**
   * Logs debug messages to the logfile, if debugging is enabled.
   *
   * @param string $message Message to be logged
   */
    public function log($message)
    {
        if ($this->enabled === true) {
            file_put_contents($this->path, "[" . date("c") . "] [$this->prefix]: $message\n", FILE_APPEND);
        }
    }
}
