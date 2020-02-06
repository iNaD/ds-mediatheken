<?php

namespace TheiNaD\DSMediatheken\Utils;

use Exception;

/**
 * Little helpers
 *
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2017-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
class Tools
{
    public static $UNSAFE_CHARACTERS = [
        'search' => [
            'ß',
            'ä',
            'Ä',
            'ö',
            'Ö',
            'ü',
            'Ü',
            '°',
            ':',
            '>',
            '<',
            '"',
            '/',
            '\\',
            '|',
            '?',
            '!',
            '*',
            "\n",
            "\r",
            '![CDATA[',
            ']]',
            'é',
        ],
        'replace' => [
            'ss',
            'ae',
            'Ae',
            'oe',
            'Oe',
            'ue',
            'Ue',
            '',
            '-',
            '',
            '',
            '',
            '-',
            '',
            '',
            '',
            '',
            '',
            ' ',
            '',
            '',
            '',
            'e',
        ],
    ];

    /** @var Logger */
    private $logger;

    /** @var Curl */
    private $curl;

    /**
     * Tools constructor.
     *
     * @param Logger $logger
     * @param Curl   $curl
     */
    public function __construct(Logger $logger, Curl $curl)
    {
        $this->logger = $logger;
        $this->curl = $curl;
    }

    /**
     * Unified curl request handling with mobile user agent
     *
     * @param string $url
     * @param array  $options
     *
     * @return null|string
     */
    public function curlRequestMobile($url, $options = [])
    {
        try {
            return $this->curl->requestMobile($url, $options);
        } catch (Exception $e) {
            $this->logger->log($e->getMessage());

            return null;
        }
    }

    /**
     * Unified curl request handling
     *
     * @param string $url     url to be requested
     * @param array  $options modify curl options
     *
     * @return null|string
     */
    public function curlRequest($url, $options = [])
    {
        try {
            return $this->curl->request($url, $options);
        } catch (Exception $e) {
            $this->logger->log($e->getMessage());

            return null;
        }
    }

    /**
     * Checks if haystack starts with needle.
     *
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    public function startsWith($haystack, $needle)
    {
        return strpos($haystack, $needle) === 0;
    }

    /**
     * Checks if haystack ends with needle.
     *
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    public function endsWith($haystack, $needle)
    {
        return (substr($haystack, -1 * strlen($needle)) === $needle);
    }

    /**
     * Based on the title build a filename with the url's file extension.
     * If no title is given, the default filename is used.
     *
     * @param string $url
     * @param string $title
     *
     * @return string
     */
    public function buildFilename($url, $title = '')
    {
        $pathinfo = pathinfo($url);

        if (!empty($title)) {
            $filename = $title . '.' . $pathinfo['extension'];
        } else {
            $filename = $pathinfo['basename'];
        }

        return $this->safeFilename($filename);
    }

    /**
     * Returns a Synology safe filename, because Umlauts currently won't work.
     *
     * @param string $filename
     *
     * @return string
     */
    public function safeFilename($filename)
    {
        return str_replace(
            self::$UNSAFE_CHARACTERS['search'],
            self::$UNSAFE_CHARACTERS['replace'],
            $filename
        );
    }

    /**
     * Builds a video title based on the given title and episode title
     *
     * @param string $title
     * @param string $episodeTitle
     *
     * @return string
     */
    public function videoTitle($title, $episodeTitle = '')
    {
        if (empty($title)) {
            return $episodeTitle;
        }

        if (!empty($episodeTitle)) {
            return $title . ' - ' . $episodeTitle;
        }

        return $title;
    }

    /**
     * Wrapper for preg_match adding default value functionality
     *
     * @param string     $pattern
     * @param string     $subject
     * @param mixed|null $default
     * @param int        $flags
     * @param int        $offset
     *
     * @return mixed|null
     */
    public function pregMatchDefault($pattern, $subject, $default = null, $flags = 0, $offset = 0)
    {
        $matches = [];

        if (preg_match($pattern, $subject, $matches, $flags, $offset) !== 1) {
            return $default;
        }

        return $matches[1];
    }

    /**
     * Wrapper for preg_match_all adding default matches value functionality
     *
     * @param string $pattern
     * @param string $subject
     * @param array  $default
     * @param int    $flags
     * @param int    $offset
     *
     * @return array
     */
    public function pregMatchAllDefault($pattern, $subject, $default = [], $flags = 0, $offset = 0)
    {
        $matches = [];

        if (preg_match_all($pattern, $subject, $matches, $flags, $offset) > 0) {
            return $matches[1];
        }

        return $default;
    }

    /**
     * If the file url starts with a generic "//" add the protocol based on baseUrl
     *
     * @param string $fileUrl
     * @param string $baseUrl
     *
     * @return string
     */
    public function addProtocolFromUrlIfMissing($fileUrl, $baseUrl)
    {
        if (!$this->startsWith($fileUrl, '//')) {
            return $fileUrl;
        }

        $protocol = substr($baseUrl, 0, strpos($baseUrl, '://'));

        return $protocol . ':' . $fileUrl;
    }

    /**
     * @param string $filename
     *
     * @return false|string
     */
    public function readGraphqlQuery($filename)
    {
        return file_get_contents(__DIR__ . '/../graphql/' . $filename);
    }
}
