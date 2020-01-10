<?php

namespace TheiNaD\DSMediatheken\Utils;

use Exception;

/**
 * Base class for all Mediatheken.
 *
 * @author Daniel Gehn <me@theinad.com>
 * @copyright 2017-2020 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
abstract class Mediathek
{
    protected static $SUPPORT_MATCHER = null;

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
     * @throws Exception
     */
    public static function supportsUrl($url)
    {
        if (static::$SUPPORT_MATCHER === null) {
            throw new Exception('A supportMatcher is mandatory');
        }

        if (is_array(static::$SUPPORT_MATCHER)) {
            if (count(static::$SUPPORT_MATCHER) === 0) {
                throw new Exception('An array supportMatcher needs at least one value');
            }

            foreach (static::$SUPPORT_MATCHER as $supports) {
                if (strpos($url, $supports) !== false) {
                    return true;
                }
            }

            return false;
        }

        if (is_string(static::$SUPPORT_MATCHER)) {
            return strpos($url, static::$SUPPORT_MATCHER) !== false;
        }

        return false;
    }
}
