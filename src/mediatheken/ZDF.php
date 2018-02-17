<?php

require_once dirname(__FILE__) . '/../utils/Mediathek.php';
require_once dirname(__FILE__) . '/../utils/Result.php';

/**
 * @author Daniel Gehn <me@theinad.com>
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
    private static $QUALITY_RATING = array(
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
    private static $MIMETYPE_RATING = array(
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
    private static $UNSUPPORTED_FACETS = array(
    'hbbtv',
    'restriction_useragent',
    );
    private static $SUPPORTED_LANGUAGES = array(
    'deu',
    );
    private static $API_BASE_URL = 'https://api.zdf.de';
    private static $BASE_URL = 'https://www.zdf.de';
    private static $JSON_ELEMENT_DOWNLOAD_INFORMATION_URL = 'http://zdf.de/rels/streams/ptmd-template';
    private static $JSON_OBJ_ELEMENT_TARGET = 'http://zdf.de/rels/target';
    private static $JSON_OBJ_ELEMENT_MAIN_VIDEO_CONTENT = 'mainVideoContent';
    private static $API_AUTH_HEADER = 'Api-Auth';
    private static $API_AUTH_PATTERN = 'Bearer {token}';
    private static $PLACEHOLDER_PLAYER_ID = '{playerId}';
    private static $PLAYER_ID = 'ngplayer_2_3';
    private static $JSON_OBJ_ELEMENT_PRIORITY_LIST = 'priorityList';
    private static $JSON_OBJ_ELEMENT_FORMITAETEN = 'formitaeten';
    private static $JSON_OBJ_ELEMENT_TITLE = 'title';
    private static $JSON_OBJ_ELEMENT_BRAND = 'http://zdf.de/rels/brand';
    private static $JSON_OBJ_ELEMENT_BRAND_TITLE = 'title';
    protected $supportMatcher = 'zdf.de';

    public function getDownloadInfo($url, $username = '', $password = '')
    {
        $result = new Result();

        $videoPage = $this->getTools()->curlRequest($url);
        if ($videoPage === null) {
            $this->getLogger()->log('Video Page (' . $url . ') using login data (' . $username . '@'
            . $password . ') could not be loaded.');
            return null;
        }

        $apiToken = $this->getApiToken($videoPage);
        $contentUrl = $this->getContentUrl($videoPage);
        if ($apiToken === null || $contentUrl === null) {
            $this->getLogger()->log('API Token (' . $apiToken . ') or content url (' . $contentUrl
            . ') could not be found.');
            return null;
        }

        $this->getLogger()->log('API Token is ' . $apiToken);

        $content = $this->apiRequest($contentUrl, $apiToken);
        if ($content === null) {
            $this->getLogger()->log('Failed to retrieve content.');
            return null;
        }

        $contentObject = json_decode($content);

        $downloadInformationUrl =
        $contentObject->{self::$JSON_OBJ_ELEMENT_MAIN_VIDEO_CONTENT}->{self::$JSON_OBJ_ELEMENT_TARGET}->{self::$JSON_ELEMENT_DOWNLOAD_INFORMATION_URL};
        $downloadUrl =
        self::$API_BASE_URL . str_replace(
            self::$PLACEHOLDER_PLAYER_ID,
            self::$PLAYER_ID,
            $downloadInformationUrl
        );

        $downloadContent = $this->apiRequest($downloadUrl, $apiToken);
        if ($downloadContent === null) {
            $this->getLogger()->log('Failed to retrieve download content from "' . $downloadUrl . '".');
            return null;
        }

        $downloadJson = json_decode($downloadContent);
        foreach ($downloadJson->{self::$JSON_OBJ_ELEMENT_PRIORITY_LIST} as $priorityListItem) {
            $result = $this->processPriorityListItem($priorityListItem, $result);
        }

        if (!$result->hasUri()) {
            $this->getLogger()->log('Failed to fetch a suitable file:' . "\n\n" . $downloadContent
            . "\n\n");
            return null;
        }

        $result->setTitle(trim(@$contentObject->{self::$JSON_OBJ_ELEMENT_BRAND}->{self::$JSON_OBJ_ELEMENT_BRAND_TITLE}));
        $result->setEpisodeTitle(trim(@$contentObject->{self::$JSON_OBJ_ELEMENT_TITLE}));

        return $result;
    }

    private function getApiToken($videoPage)
    {
        if (preg_match('#"apiToken": "(.*?)",#i', $videoPage, $match) !== 1) {
            return null;
        }
        return $match[1];
    }

    private function getContentUrl($videoPage)
    {
        if (preg_match('#"content": "(.*?)",#i', $videoPage, $match) !== 1) {
            return null;
        }
        return $match[1];
    }

    private function apiRequest($url, $apiToken)
    {
        $this->getLogger()->log('API Request to "' . $url . '" with token "' . $apiToken . '"');

        return $this->getTools()->curlRequest($url, array(
        CURLOPT_HTTPHEADER => array(
        self::$API_AUTH_HEADER . ': ' . str_replace('{token}', $apiToken, self::$API_AUTH_PATTERN)
        )
        ));
    }

    private function processPriorityListItem($priorityListItem, $result)
    {
        foreach ($priorityListItem->{self::$JSON_OBJ_ELEMENT_FORMITAETEN} as $formitaet) {
            if ($this->isFacetSupported($formitaet) === false) {
                continue;
            }

            $mimeTypeRating = $this->getMimeTypeRating($formitaet);
            if ($mimeTypeRating <= $result->getMimeTypeRating()) {
                continue;
            }

            foreach ($formitaet->qualities as $quality) {
                $result = $this->processQuality($quality, $mimeTypeRating, $result);
            }
        }

        return $result;
    }

    private function isFacetSupported($formitaet)
    {
        foreach ($formitaet->facets as $facet) {
            if (in_array($facet, self::$UNSUPPORTED_FACETS)) {
                return false;
            }
        }

        return true;
    }

    private function getMimeTypeRating($formitaet)
    {
        if (array_key_exists($formitaet->mimeType, self::$MIMETYPE_RATING) === false) {
            $this->getLogger()->log('Unknown Mime Type ' . $formitaet->mimeType);
            return -1;
        }

        return self::$MIMETYPE_RATING[$formitaet->mimeType];
    }

    private function processQuality($quality, $mimeTypeRating, $result)
    {
        $qualityRating = $this->getQualityRating($quality);
        if ($qualityRating <= $result->getQualityRating()) {
            return $result;
        }

        foreach ($quality->audio->tracks as $track) {
            if ($this->isLanguageSupported($track) === false) {
                $this->getLogger()->log('Unknown language ' . $track->language);
                continue;
            }

            $result = new Result();
            $result->setUri($track->uri);
            $result->setMimeTypeRating($mimeTypeRating);
            $result->setQualityRating($qualityRating);
        }

        return $result;
    }

    private function getQualityRating($quality)
    {
        if (array_key_exists($quality->quality, self::$QUALITY_RATING) === false) {
            $this->getLogger()->log('Unknown quality ' . $quality->quality);
            return -1;
        }
        return self::$QUALITY_RATING[$quality->quality];
    }

    private function isLanguageSupported($track)
    {
        return in_array($track->language, self::$SUPPORTED_LANGUAGES);
    }
}
