<?php

namespace TheiNaD\DSMediatheken\Utils;

/**
 * Little helpers
 *
 * @author Daniel Gehn <me@theinad.com>
 * @copyright 2017-2018 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
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
            '',
            '',
            '',
            '',
            '',
            '',
            ' ',
            '',
            '',
            '',
        ],
    ];

    private $logger;
    private $curl;

    /**
     * Tools constructor.
     *
     * @param Logger $logger
     * @param Curl $curl
     */
    public function __construct(Logger $logger, Curl $curl)
    {
        $this->logger = $logger;
        $this->curl = $curl;
    }

    public function curlRequestMobile($url, $options = [])
    {
        try {
            return $this->curl->requestMobile($url, $options);
        } catch (\Exception $e) {
            $this->logger->log($e->getMessage());
            return null;
        }
    }

    /**
     * Unified curl request handling
     *
     * @param string $url url to be requested
     * @param array $options modify curl options
     * @return null | string
     */
    public function curlRequest($url, $options = [])
    {
        try {
            return $this->curl->request($url, $options);
        } catch (\Exception $e) {
            $this->logger->log($e->getMessage());
            return null;
        }
    }

    /**
     * Checks if haystack starts with needle.
     *
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public function startsWith($haystack, $needle)
    {
        return (substr($haystack, 0, strlen($needle)) === $needle);
    }

    /**
     * Checks if haystack ends with needle.
     *
     * @param $haystack
     * @param $needle
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

    public function pregMatchDefault($pattern, $subject, $default = null, $flags = 0, $offset = 0)
    {
        $matches = [];

        if (preg_match($pattern, $subject, $matches, $flags, $offset) !== 1) {
            return $default;
        }
        return $matches[1];
    }

    public function pregMatchAllDefault($pattern, $subject, $default = [], $flags = 0, $offset = 0)
    {
        $matches = [];

        if (preg_match($pattern, $subject, $matches, $flags, $offset) > 0) {
            return array_slice($matches, 1);
        }
        return $default;
    }

    public function addProtocolFromUrlIfMissing($bestQualityUrl, $url)
    {
        if (!$this->startsWith($bestQualityUrl, '//')) {
            return $bestQualityUrl;
        }

        $protocol = substr($url, 0, strpos($url, '://'));
        return $protocol . ':' . $bestQualityUrl;
    }
}
