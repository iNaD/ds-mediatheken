<?php

/**
 * Base class for all Mediatheken.
 *
 * @author Daniel Gehn <me@theinad.com>
 * @copyright 2017 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
abstract class Mediathek
{

    protected $supportMatcher = null;
    private $logger;
    private $tools;

    public function __construct(Logger $logger, Tools $tools)
    {
        $this->logger = $logger;
        $this->tools = $tools;
    }

    abstract public function getDownloadInfo($url, $username, $password);

  /**
   * @return Logger
   */
    public function getLogger()
    {
        return $this->logger;
    }

  /**
   * @return Tools
   */
    public function getTools()
    {
        return $this->tools;
    }

    public function supportsUrl($url)
    {
        if ($this->supportMatcher === null) {
            throw new Exception('A supportMatcher is mandatory');
        }

        if (is_array($this->supportMatcher)) {
            if (count($this->supportMatcher) === 0) {
                throw new Exception('An array supportMatcher needs at least one value');
            }

            foreach ($this->supportMatcher as $supports) {
                if (strpos($url, $supports) !== false) {
                    return true;
                }
            }

            return false;
        }

        if (is_string($this->supportMatcher)) {
            return strpos($url, $this->supportMatcher) !== false;
        }

        return false;
    }
}
