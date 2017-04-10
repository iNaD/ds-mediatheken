<?php

require_once dirname(__FILE__) . '/../utils/Mediathek.php';

/**
 * @author Daniel Gehn <me@theinad.com>
 * @version 0.0.1
 * @copyright 2017 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class ZDF extends Mediathek
{

  /**
   * Maps Qualities to ratings.
   *
   * A higher rating means the quality is preferred.
   * A rating of -1 means the quality will be ignored.
   *
   * @var array
   */
  protected static $QUALITY_RATING = array(
    'low' => 0,
    'med' => 1,
    'high' => 2,
    'veryhigh' => 3,
  );

  /**
   * Maps Mimetypes to ratings.
   *
   * A higher rating means the mimetype is preferred.
   * A rating of -1 means the mimetype will be ignored.
   *
   * @var array
   */
  protected static $MIMETYPE_RATING = array(
    'application/x-mpegURL' => -1,
    'application/f4m+xml' => -1,
    'video/webm' => 1,
    'video/mp4' => 2,
  );

  /**
   * Facets containing this type will be completely ignored.
   *
   * @var array
   */
  protected static $UNSUPPORTED_FACETS = array(
    'hbbtv',
    'restriction_useragent',
  );

  protected static $SUPPORTED_LANGUAGES = array(
    'deu',
  );

  protected static $API_BASE_URL = 'https://api.zdf.de';
  protected static $BASE_URL = 'https://www.zdf.de';
  protected static $JSON_ELEMENT_DOWNLOAD_INFORMATION_URL = 'http://zdf.de/rels/streams/ptmd-template';
  protected static $JSON_OBJ_ELEMENT_TARGET = 'http://zdf.de/rels/target';
  protected static $JSON_OBJ_ELEMENT_MAIN_VIDEO_CONTENT = 'mainVideoContent';
  protected static $API_AUTH_HEADER = 'Api-Auth';
  protected static $API_AUTH_PATTERN = 'Bearer {token}';
  protected static $PLACEHOLDER_PLAYER_ID = '{playerId}';
  protected static $PLAYER_ID = 'ngplayer_2_3';
  protected static $JSON_OBJ_ELEMENT_PRIORITY_LIST = 'priorityList';
  protected static $JSON_OBJ_ELEMENT_FORMITAETEN = 'formitaeten';

  public function getDownloadInfo($url, $username = '', $password = '')
  {
    $videoPage = $this->tools->curlRequest($url);

    if ($videoPage === null) {
      $this->logger->log('Video Page (' . $url . ') using login data (' . $username . '@'
        . $password . ') could not be loaded.');
      return false;
    }

    $apiToken = $this->getApiToken($videoPage);
    $contentUrl = $this->getContentUrl($videoPage);

    if ($apiToken === null || $contentUrl === null) {
      $this->logger->log('API Token (' . $apiToken . ') or content url (' . $contentUrl
        . ') could not be found.');
      return false;
    }

    $this->logger->log('Api Token is ' . $apiToken);

    $content = $this->apiRequest($contentUrl, $apiToken);

    if ($content === null) {
      $this->logger->log('Failed to retrieve content.');
      return false;
    }

    $contentObject = json_decode($content);

    $downloadInformationUrl =
      $contentObject->{self::$JSON_OBJ_ELEMENT_MAIN_VIDEO_CONTENT}->{self::$JSON_OBJ_ELEMENT_TARGET}->{self::$JSON_ELEMENT_DOWNLOAD_INFORMATION_URL};
    $downloadUrl =
      self::$API_BASE_URL . str_replace(self::$PLACEHOLDER_PLAYER_ID, self::$PLAYER_ID,
        $downloadInformationUrl);

    $downloadContent = $this->apiRequest($downloadUrl, $apiToken);

    if ($downloadContent === null) {
      $this->logger->log('Failed to retrieve download content from "' . $downloadUrl . '".');
      return false;
    }

    $downloadJson = json_decode($downloadContent);

    $result = array(
      'mimeTypeRating' => -1,
      'qualityRating' => -1,
      'uri' => null,
    );

    foreach ($downloadJson->{self::$JSON_OBJ_ELEMENT_PRIORITY_LIST} as $priorityListItem) {
      foreach ($priorityListItem->{self::$JSON_OBJ_ELEMENT_FORMITAETEN} as $formitaet) {
        $hasUnsupportedFacet = false;

        foreach ($formitaet->facets as $facet) {
          if (in_array($facet, self::$UNSUPPORTED_FACETS)) {
            $hasUnsupportedFacet = true;
            break;
          }
        }

        if ($hasUnsupportedFacet === true) {
          continue;
        }

        if (array_key_exists($formitaet->mimeType, self::$MIMETYPE_RATING) === false) {
          $this->logger->log('Unknown Mime Type ' . $formitaet->mimeType);
          continue;
        }

        $mimeTypeRating = self::$MIMETYPE_RATING[$formitaet->mimeType];

        if ($mimeTypeRating <= $result['mimeTypeRating']) {
          continue;
        }

        foreach ($formitaet->qualities as $quality) {
          if (array_key_exists($quality->quality, self::$QUALITY_RATING) === false) {
            $this->logger->log('Unknown quality ' . $quality->quality);
            continue;
          }

          $qualityRating = self::$QUALITY_RATING[$quality->quality];

          if ($qualityRating <= $result['qualityRating']) {
            continue;
          }

          foreach ($quality->audio->tracks as $track) {
            if (in_array($track->language, self::$SUPPORTED_LANGUAGES) === false) {
              $this->logger->log('Unknown language ' . $track->language);
              continue;
            }

            $result['uri'] = $track->uri;
            $result['mimeTypeRating'] = $mimeTypeRating;
            $result['qualityRating'] = $qualityRating;
          }
        }
      }
    }

    if($result['uri'] === null) {
      $this->logger->log('Failed to fetch a suitable file:' . "\n\n" . $downloadContent . "\n\n");
      return false;
    }

    return $result;
  }

  protected function getApiToken($videoPage)
  {
    if (preg_match('#"apiToken": "(.*?)",#i', $videoPage, $match) !== 1) {
      return null;
    }

    return $match[1];
  }

  protected function getContentUrl($videoPage)
  {
    if (preg_match('#"content": "(.*?)",#i', $videoPage, $match) !== 1) {
      return null;
    }

    return $match[1];
  }

  protected function apiRequest($url, $apiToken)
  {
    $this->logger->log('API Request to "' . $url . '" with token "' . $apiToken . '"');

    return $this->tools->curlRequest($url, array(
      CURLOPT_HTTPHEADER => array(
        self::$API_AUTH_HEADER . ': ' . str_replace('{token}', $apiToken, self::$API_AUTH_PATTERN)
      )
    ));
  }

}
