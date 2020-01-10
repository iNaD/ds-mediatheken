<?php

namespace TheiNaD\DSMediatheken\Mediatheken;

use TheiNaD\DSMediatheken\Utils\Mediathek;
use TheiNaD\DSMediatheken\Utils\Result;

/**
 * @author Daniel Gehn <me@theinad.com>
 * @copyright 2017-2020 Daniel Gehn
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
    protected static $QUALITY_RATING = [
        'low' => 0,
        'med' => 1,
        'high' => 2,
        'veryhigh' => 3,
    ];
    /**
     * Maps Mimetypes to ratings.
     *
     * A higher rating means the mimetype is preferred.
     * A rating of -1 means the mimetype will be ignored.
     *
     * @var array
     */
    protected static $MIMETYPE_RATING = [
        'application/x-mpegURL' => -1,
        'application/f4m+xml' => -1,
        'video/webm' => 1,
        'video/mp4' => 2,
    ];
    /**
     * Facets containing this type will be completely ignored.
     *
     * @var array
     */
    protected static $UNSUPPORTED_FACETS = [
        'hbbtv',
        'restriction_useragent',
    ];
    protected static $SUPPORTED_LANGUAGES = [
        'deu',
    ];
    protected static $API_BASE_URL = 'https://api.zdf.de';
    protected static $JSON_ELEMENT_DOWNLOAD_INFORMATION_URL = 'http://zdf.de/rels/streams/ptmd-template';
    protected static $JSON_OBJ_ELEMENT_TARGET = 'http://zdf.de/rels/target';
    protected static $JSON_OBJ_ELEMENT_MAIN_VIDEO_CONTENT = 'mainVideoContent';
    protected static $API_AUTH_HEADER = 'Api-Auth';
    protected static $API_AUTH_PATTERN = 'Bearer {token}';
    protected static $PLACEHOLDER_PLAYER_ID = '{playerId}';
    protected static $PLAYER_ID = 'ngplayer_2_3';
    protected static $JSON_OBJ_ELEMENT_PRIORITY_LIST = 'priorityList';
    protected static $JSON_OBJ_ELEMENT_FORMITAETEN = 'formitaeten';
    protected static $JSON_OBJ_ELEMENT_TITLE = 'title';
    protected static $JSON_OBJ_ELEMENT_BRAND = 'http://zdf.de/rels/brand';
    protected static $JSON_OBJ_ELEMENT_BRAND_TITLE = 'title';
    protected static $SUPPORT_MATCHER = 'zdf.de';

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

        $contentObject = json_decode($content, false);

        $downloadInformationUrl =
            $contentObject
                ->{static::$JSON_OBJ_ELEMENT_MAIN_VIDEO_CONTENT}
                ->{static::$JSON_OBJ_ELEMENT_TARGET}
                ->{static::$JSON_ELEMENT_DOWNLOAD_INFORMATION_URL};

        $downloadUrl =
            static::$API_BASE_URL . str_replace(
                static::$PLACEHOLDER_PLAYER_ID,
                static::$PLAYER_ID,
                $downloadInformationUrl
            );

        $downloadContent = $this->apiRequest($downloadUrl, $apiToken);
        if ($downloadContent === null) {
            $this->getLogger()->log('Failed to retrieve download content from "' . $downloadUrl . '".');
            return null;
        }

        $downloadJson = json_decode($downloadContent, false);
        foreach ($downloadJson->{static::$JSON_OBJ_ELEMENT_PRIORITY_LIST} as $priorityListItem) {
            $result = $this->processPriorityListItem($priorityListItem, $result);
        }

        if (!$result->hasUri()) {
            $this->getLogger()->log('Failed to fetch a suitable file:' . "\n\n" . $downloadContent
                . "\n\n");
            return null;
        }

        $result->setTitle($this->getTitle($contentObject));
        $result->setEpisodeTitle($this->getEpisodeTitle($contentObject));
        $result->setUri($this->getTools()->addProtocolFromUrlIfMissing($result->getUri(), $url));

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
        $this->getLogger()->log(
            sprintf(
                'API Request to "%s" with token "%s"',
                $url,
                $apiToken
            )
        );

        return $this->getTools()->curlRequest($url, [
            CURLOPT_HTTPHEADER => [
                static::$API_AUTH_HEADER . ': ' . str_replace('{token}', $apiToken, static::$API_AUTH_PATTERN)
            ]
        ]);
    }

    protected function processPriorityListItem($priorityListItem, Result $result)
    {
        foreach ($priorityListItem->{static::$JSON_OBJ_ELEMENT_FORMITAETEN} as $formitaet) {
            $this->getLogger()->log(sprintf('Formität: %s', join(', ', $formitaet->facets)));

            if ($this->isFacetSupported($formitaet) === false) {
                $this->getLogger()->log(sprintf('Unsupported facets %s', join(', ', $formitaet->facets)));
                continue;
            }

            $mimeTypeRating = $this->getMimeTypeRating($formitaet);
            if ($mimeTypeRating <= $result->getMimeTypeRating()) {
                $this->getLogger()->log(
                    sprintf(
                        'Mimetype Rating "%d" is lower or equal to previous "%d"',
                        $mimeTypeRating,
                        $result->getMimeTypeRating()
                    )
                );
                continue;
            }

            foreach ($formitaet->qualities as $quality) {
                $result = $this->processQuality($quality, $mimeTypeRating, $result);
            }
        }

        return $result;
    }

    protected function isFacetSupported($formitaet)
    {
        foreach ($formitaet->facets as $facet) {
            if (in_array($facet, static::$UNSUPPORTED_FACETS)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $formitaet
     * @return int
     */
    protected function getMimeTypeRating($formitaet)
    {
        if (array_key_exists($formitaet->mimeType, static::$MIMETYPE_RATING) === false) {
            $this->getLogger()->log('Unknown Mime Type ' . $formitaet->mimeType);
            return -1;
        }

        return static::$MIMETYPE_RATING[$formitaet->mimeType];
    }

    protected function processQuality($quality, $mimeTypeRating, Result $result)
    {
        $qualityRating = $this->getQualityRating($quality);
        if ($qualityRating <= $result->getQualityRating()) {
            $this->getLogger()->log(
                sprintf(
                    'Quality Rating "%d" is lower than previous of "%d"',
                    $qualityRating,
                    $result->getQualityRating()
                )
            );

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

    /**
     * @param $quality
     * @return int
     */
    protected function getQualityRating($quality)
    {
        if (array_key_exists($quality->quality, static::$QUALITY_RATING) === false) {
            $this->getLogger()->log('Unknown quality ' . $quality->quality);
            return -1;
        }
        return static::$QUALITY_RATING[$quality->quality];
    }

    protected function isLanguageSupported($track)
    {
        return in_array($track->language, static::$SUPPORTED_LANGUAGES);
    }

    protected function getTitle($contentObject)
    {
        $title = @$contentObject
            ->{static::$JSON_OBJ_ELEMENT_BRAND}
            ->{static::$JSON_OBJ_ELEMENT_BRAND_TITLE};

        return trim($title);
    }

    protected function getEpisodeTitle($contentObject)
    {
        $episodeTitle = @$contentObject->{static::$JSON_OBJ_ELEMENT_TITLE};

        return trim($episodeTitle);
    }
}
