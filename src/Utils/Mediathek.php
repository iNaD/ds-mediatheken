<?php

namespace TheiNaD\DSMediatheken\Utils;

use RuntimeException;

/**
 * Base class for all Mediatheken.
 *
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2017-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
abstract class Mediathek
{
    protected static $SUPPORT_MATCHER;

    /** @var Logger */
    private $logger;

    /** @var Tools */
    private $tools;

    public function __construct(Logger $logger, Tools $tools)
    {
        $this->logger = $logger;
        $this->tools = $tools;
    }

    /**
     * @param string $url
     * @param string $username
     * @param string $password
     *
     * @return Result|null
     */
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
     *
     * @return boolean
     *
     * @throws RuntimeException
     */
    public static function supportsUrl($url)
    {
        if (static::$SUPPORT_MATCHER === null) {
            throw new RuntimeException('A supportMatcher is mandatory');
        }

        if (is_array(static::$SUPPORT_MATCHER)) {
            if (count(static::$SUPPORT_MATCHER) === 0) {
                throw new RuntimeException('An array supportMatcher needs at least one value');
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
