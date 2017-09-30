<?php

/**
 * Little helpers
 *
 * @author Daniel Gehn <me@theinad.com>
 * @copyright 2017 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class Tools
{

  public static $UNSAFE_CHARACTERS = array(
    'search' => array(
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
    ),
    'replace' => array(
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
    ),
  );

  private $logger;

  /**
   * Tools constructor.
   */
  public function __construct(Logger $logger)
  {
    $this->logger = $logger;
  }


  /**
   * Unified curl request handling
   *
   * @param string $url url to be requested
   * @param array $options modify curl options
   * @return null | string
   */
  public function curlRequest($url, $options = array())
  {
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    foreach ($options as $option => $value) {
      curl_setopt($curl, $option, $value);
    }

    $result = curl_exec($curl);

    if (!$result) {
      $this->logger->log('Failed to retrieve XML. Error Info: ' . curl_error($curl));
      return null;
    }

    curl_close($curl);

    return $result;
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
    return str_replace(self::$UNSAFE_CHARACTERS['search'], self::$UNSAFE_CHARACTERS['replace'],
      $filename);
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

}
