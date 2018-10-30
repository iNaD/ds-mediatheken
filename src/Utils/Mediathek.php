<?php

namespace TheiNaD\DSMediatheken\Utils;

/**
 * Base class for all Mediatheken.
 *
 * @author Daniel Gehn <me@theinad.com>
 * @copyright 2017-2018 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
abstract class Mediathek
{
    protected static $supportMatcher = null;
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

    /**
     * Returns if the given url is supported.
     *
     * @param string $url
     * @return boolean
     */
    public static function supportsUrl($url)
    {
        if (static::$supportMatcher === null) {
            throw new Exception('A supportMatcher is mandatory');
        }

        if (is_array(static::$supportMatcher)) {
            if (count(static::$supportMatcher) === 0) {
                throw new Exception('An array supportMatcher needs at least one value');
            }

            foreach (static::$supportMatcher as $supports) {
                if (strpos($url, $supports) !== false) {
                    return true;
                }
            }

            return false;
        }

        if (is_string(static::$supportMatcher)) {
            return strpos($url, static::$supportMatcher) !== false;
        }

        return false;
    }
}
