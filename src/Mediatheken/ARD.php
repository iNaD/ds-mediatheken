<?php

namespace TheiNaD\DSMediatheken\Mediatheken;

use TheiNaD\DSMediatheken\Utils\Mediathek;
use TheiNaD\DSMediatheken\Utils\Result;

/**
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2017-2022 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
class ARD extends Mediathek
{
    public static $API_BASE_URL = 'https://api.ardmediathek.de/page-gateway/pages/ard/item/';
    protected static $SUPPORT_MATCHER = ['ardmediathek.de', 'mediathek.daserste.de'];
    protected static $PLAYER_WIDGET_TYPE = 'player_ondemand';
    protected static $WANTED_MIME_TYPE = 'video/mp4';

    /**
     * Suffix appended to the document id of "grouping" pages (e.g. films). The actual playable
     * item is reachable under the same id with this suffix removed.
     *
     * @var string
     */
    protected static $GROUPING_SUFFIX = '-movie';

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

        $documentId = $this->getDocumentIdFromUrl($url);
        if ($documentId === null) {
            $this->getLogger()->log('no documentId found in ' . $url);

            return null;
        }

        $apiData = $this->getApiData($documentId);
        if ($apiData === null) {
            $this->getLogger()->log('could not retrieve apiData');

            return null;
        }

        foreach ($apiData->widgets as $widget) {
            if ($widget->type !== self::$PLAYER_WIDGET_TYPE) {
                continue;
            }

            foreach ($this->getMediaList($widget) as $media) {
                if (!$this->isUsableMedia($media)) {
                    continue;
                }

                $quality = $this->getMediaQuality($media);
                if ($quality > $result->getQualityRating()) {
                    $this->getLogger()->log(sprintf('Found stream with quality "%s"', $quality));

                    $result = new Result();
                    $result->setQualityRating($quality);
                    $result->setUri($media->url);
                }
            }

            if ($result->hasUri()) {
                $result = $this->addTitle($widget, $result);
            }
        }

        if (!$result->hasUri()) {
            return null;
        }

        $this->getLogger()->log(sprintf(
            'Url "%s" won with quality of "%s"',
            $result->getUri(),
            $result->getQualityRating()
        ));

        $result->setUri($this->getTools()->addProtocolFromUrlIfMissing($result->getUri(), $url));

        return $result;
    }

    /**
     * @param string $url
     *
     * @return string|null
     */
    protected function getDocumentIdFromUrl($url)
    {
        $documentId = $this->getTools()->pregMatchDefault('#/([^/]+?)/?$#i', $url, null);
        if ($documentId === null) {
            return null;
        }

        return $this->normalizeDocumentId($documentId);
    }

    /**
     * Some pages (e.g. films) link to a "grouping" document id that the item API does not know
     * about. The playable item shares the same base64 encoded id, just without the grouping
     * suffix, so we strip it before querying the API.
     *
     * @param string $documentId
     *
     * @return string
     */
    protected function normalizeDocumentId($documentId)
    {
        $decoded = base64_decode($documentId, true);
        if ($decoded === false || !$this->getTools()->endsWith($decoded, self::$GROUPING_SUFFIX)) {
            return $documentId;
        }

        $decoded = substr($decoded, 0, -strlen(self::$GROUPING_SUFFIX));
        $normalized = rtrim(base64_encode($decoded), '=');

        $this->getLogger()->log(sprintf('Normalized grouping documentId to "%s"', $normalized));

        return $normalized;
    }

    /**
     * @param string $documentId
     *
     * @return object|null
     */
    protected function getApiData($documentId)
    {
        return json_decode($this->getTools()->curlRequest(self::$API_BASE_URL . $documentId), false);
    }

    /**
     * Collects all media entries of a player widget across the available streams.
     *
     * @param object $widget
     *
     * @return array
     */
    protected function getMediaList($widget)
    {
        if (!isset($widget->mediaCollection->embedded->streams)) {
            return [];
        }

        $mediaList = [];
        foreach ($widget->mediaCollection->embedded->streams as $stream) {
            if (!isset($stream->media)) {
                continue;
            }

            foreach ($stream->media as $media) {
                $mediaList[] = $media;
            }
        }

        return $mediaList;
    }

    /**
     * @param object $media
     *
     * @return bool
     */
    protected function isUsableMedia($media)
    {
        return isset($media->url, $media->mimeType) && $media->mimeType === self::$WANTED_MIME_TYPE;
    }

    /**
     * The horizontal resolution is used as a quality indicator, higher meaning better.
     *
     * @param object $media
     *
     * @return int
     */
    protected function getMediaQuality($media)
    {
        return isset($media->maxHResolutionPx) ? (int)$media->maxHResolutionPx : 0;
    }

    /**
     * @param object $widget
     * @param Result $result
     *
     * @return Result
     */
    protected function addTitle($widget, Result $result)
    {
        if (isset($widget->show->title)) {
            $result->setTitle($widget->show->title);
        }

        if (isset($widget->title)) {
            $result->setEpisodeTitle($widget->title);
        }

        return $result;
    }
}
