<?php

namespace TheiNaD\DSMediatheken\Mediatheken;

use TheiNaD\DSMediatheken\Utils\Mediathek;
use TheiNaD\DSMediatheken\Utils\Result;

/**
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2017-2022 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
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

    /**
     * @param string $url
     * @param string $username
     * @param string $password
     *
     * @return Result|null
     */
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
        if ($apiToken === null) {
            $this->getLogger()->log('API Token could not be found.');

            return null;
        }

        $this->getLogger()->log('API Token is ' . $apiToken);

        // Current zdf.de pages embed the ptmd template directly. Older pages (e.g. 3sat.de) only
        // reference a content document that has to be fetched to resolve the template.
        $contentObject = null;
        $ptmdTemplate = $this->getPtmdTemplate($videoPage);
        if ($ptmdTemplate === null) {
            $contentObject = $this->getContentObject($videoPage, $apiToken);
            if ($contentObject !== null) {
                $ptmdTemplate = $this->getPtmdTemplateFromContent($contentObject);
            }
        }

        if ($ptmdTemplate === null) {
            $this->getLogger()->log('ptmd template could not be found.');

            return null;
        }

        $downloadUrl =
            static::$API_BASE_URL . str_replace(
                static::$PLACEHOLDER_PLAYER_ID,
                static::$PLAYER_ID,
                $ptmdTemplate
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

        if ($contentObject !== null) {
            $result->setTitle($this->getTitle($contentObject));
            $result->setEpisodeTitle($this->getEpisodeTitle($contentObject));
        } else {
            $result->setTitle($this->getTitleFromPage($videoPage));
            $result->setEpisodeTitle($this->getEpisodeTitleFromPage($videoPage));
        }
        $result->setUri($this->getTools()->addProtocolFromUrlIfMissing($result->getUri(), $url));

        return $result;
    }

    /**
     * Extracts the player's api token. Current zdf.de pages embed the JSON as an escaped string
     * (e.g. \"videoToken\":{\"apiToken\":\"...\"}); older pages (3sat.de) expose "apiToken": "...".
     *
     * @param string $videoPage
     *
     * @return string|null
     */
    protected function getApiToken($videoPage)
    {
        $apiToken = $this->getTools()->pregMatchDefault(
            '#videoToken\\\\?":\{\\\\?"apiToken\\\\?":\\\\?"([A-Za-z0-9]+)#i',
            $videoPage
        );
        if ($apiToken !== null) {
            return $apiToken;
        }

        return $this->getTools()->pregMatchDefault('#"apiToken": "(.*?)",#i', $videoPage);
    }

    /**
     * Extracts the ptmd template (relative path, still containing the {playerId} placeholder)
     * embedded directly in current zdf.de pages. Returns null on older pages that reference a
     * content document instead.
     *
     * @param string $videoPage
     *
     * @return string|null
     */
    protected function getPtmdTemplate($videoPage)
    {
        return $this->getTools()->pregMatchDefault(
            '#ptmdTemplate\\\\?":\\\\?"([^"\\\\]+)#i',
            $videoPage
        );
    }

    /**
     * Loads and decodes the content document referenced by older pages (3sat.de).
     *
     * @param string $videoPage
     * @param string $apiToken
     *
     * @return object|null
     */
    protected function getContentObject($videoPage, $apiToken)
    {
        $contentUrl = $this->getContentUrl($videoPage);
        if ($contentUrl === null) {
            return null;
        }

        $content = $this->apiRequest($contentUrl, $apiToken);
        if ($content === null) {
            return null;
        }

        return json_decode($content, false);
    }

    /**
     * @param string $videoPage
     *
     * @return string|null
     */
    protected function getContentUrl($videoPage)
    {
        return $this->getTools()->pregMatchDefault('#"content": "(.*?)",#i', $videoPage);
    }

    /**
     * Resolves the ptmd template from a content document.
     *
     * @param object $contentObject
     *
     * @return string|null
     */
    protected function getPtmdTemplateFromContent($contentObject)
    {
        if (
            !isset($contentObject->{static::$JSON_OBJ_ELEMENT_MAIN_VIDEO_CONTENT}
                ->{static::$JSON_OBJ_ELEMENT_TARGET})
        ) {
            return null;
        }

        $mainVideoContentTarget = $contentObject
            ->{static::$JSON_OBJ_ELEMENT_MAIN_VIDEO_CONTENT}
            ->{static::$JSON_OBJ_ELEMENT_TARGET};

        if (property_exists($mainVideoContentTarget, static::$JSON_ELEMENT_DOWNLOAD_INFORMATION_URL)) {
            return $mainVideoContentTarget->{static::$JSON_ELEMENT_DOWNLOAD_INFORMATION_URL};
        }

        if (isset($mainVideoContentTarget->streams->default->{static::$JSON_ELEMENT_DOWNLOAD_INFORMATION_URL})) {
            return $mainVideoContentTarget
                ->streams
                ->default
                ->{static::$JSON_ELEMENT_DOWNLOAD_INFORMATION_URL};
        }

        return null;
    }

    /**
     * @param string $url
     * @param string $apiToken
     *
     * @return string|null
     */
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
                static::$API_AUTH_HEADER . ': ' . str_replace('{token}', $apiToken, static::$API_AUTH_PATTERN),
            ],
        ]);
    }

    /**
     * @param object $priorityListItem
     * @param Result $result
     *
     * @return Result
     */
    protected function processPriorityListItem($priorityListItem, Result $result)
    {
        foreach ($priorityListItem->{static::$JSON_OBJ_ELEMENT_FORMITAETEN} as $formitaet) {
            $this->getLogger()->log(sprintf('Formität: %s', implode(', ', $formitaet->facets)));

            if ($this->isFacetSupported($formitaet) === false) {
                $this->getLogger()->log(sprintf('Unsupported facets %s', implode(', ', $formitaet->facets)));
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

    /**
     * @param object $formitaet
     *
     * @return bool
     */
    protected function isFacetSupported($formitaet)
    {
        foreach ($formitaet->facets as $facet) {
            if (in_array($facet, static::$UNSUPPORTED_FACETS, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param object $formitaet
     *
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

    /**
     * @param object $quality
     * @param int    $mimeTypeRating
     * @param Result $result
     *
     * @return Result
     */
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

            if ($track->class !== 'main') {
                $this->getLogger()->log('Is not class "main" ' . $track->class);
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
     *
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

    /**
     * @param object $track
     *
     * @return bool
     */
    protected function isLanguageSupported($track)
    {
        return in_array($track->language, static::$SUPPORTED_LANGUAGES, true);
    }

    /**
     * Title (show/brand) from a content document (older pages).
     *
     * @param object $contentObject
     *
     * @return string
     */
    protected function getTitle($contentObject)
    {
        $title = @$contentObject
            ->{static::$JSON_OBJ_ELEMENT_BRAND}
            ->{static::$JSON_OBJ_ELEMENT_BRAND_TITLE};

        return trim($title);
    }

    /**
     * Episode title from a content document (older pages).
     *
     * @param object $contentObject
     *
     * @return string
     */
    protected function getEpisodeTitle($contentObject)
    {
        $episodeTitle = @$contentObject->{static::$JSON_OBJ_ELEMENT_TITLE};

        return trim($episodeTitle);
    }

    /**
     * Title from a current zdf.de page payload (\"video\":{...,\"title\":\"...\"}).
     *
     * @param string $videoPage
     *
     * @return string
     */
    protected function getTitleFromPage($videoPage)
    {
        $title = $this->getTools()->pregMatchDefault(
            '#"video\\\\?":\{(?:\\\\?"__typename\\\\?":\\\\?"Video\\\\?",)?\\\\?"title\\\\?":\\\\?"([^"\\\\]+)#i',
            $videoPage,
            ''
        );

        return trim($title);
    }

    /**
     * Current zdf.de pages do not expose a separate episode title next to the program title, so
     * the title alone is used to build the filename.
     *
     * @param string $videoPage
     *
     * @return string
     */
    protected function getEpisodeTitleFromPage($videoPage)
    {
        return '';
    }
}
