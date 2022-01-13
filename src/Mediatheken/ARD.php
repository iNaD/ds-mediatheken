<?php

namespace TheiNaD\DSMediatheken\Mediatheken;

use TheiNaD\DSMediatheken\Utils\Mediathek;
use TheiNaD\DSMediatheken\Utils\Result;

/**
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2017-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
class ARD extends Mediathek
{
    protected static $API_BASE_URL = 'https://api.ardmediathek.de/page-gateway/pages/ard/item/';
    protected static $VALID_QUALITIES = [0, 1, 2, 3, 4];
    protected static $SUPPORT_MATCHER = ['ardmediathek.de', 'mediathek.daserste.de'];
    protected static $PLAYER_WIDGET_TYPE = 'player_ondemand';

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

            foreach ($widget->mediaCollection->embedded->_mediaArray as $media) {
                foreach ($media->_mediaStreamArray as $mediaStream) {
                    if (
                        $this->mediaStreamHasNeededProperties($mediaStream) &&
                        $this->mediaStreamHasValidQuality($mediaStream)
                    ) {
                        if ($mediaStream->_quality > $result->getQualityRating()) {
                            $this->getLogger()->log(sprintf('Found stream with quality "%s"', $mediaStream->_quality));

                            $stream = $this->getHighestQualityStream($mediaStream->_stream);
                            if ($stream !== null) {
                                $result = new Result();
                                $result->setQualityRating($mediaStream->_quality);
                                $result->setUri($stream);
                            }
                        }
                    }
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
        return $this->getTools()->pregMatchDefault('#/([^/]+)/?$#i', $url, null);
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
     * @param object $mediaStream
     *
     * @return bool
     */
    protected function mediaStreamHasNeededProperties($mediaStream)
    {
        return property_exists($mediaStream, '_stream') && property_exists($mediaStream, '_quality');
    }

    /**
     * @param object $mediaStream
     *
     * @return bool
     */
    protected function mediaStreamHasValidQuality($mediaStream)
    {
        return in_array($mediaStream->_quality, self::$VALID_QUALITIES, true);
    }

    /**
     * @param mixed $streams
     *
     * @return mixed
     */
    protected function getHighestQualityStream($streams)
    {
        if (!is_array($streams)) {
            return $streams;
        }

        $this->getLogger()->log('Multiple streams found for single quality');

        $hqStream = [
            'quality' => 0,
            'url' => null,
        ];

        foreach ($streams as $stream) {
            $quality = $this->getQualityFromStreamUrl($stream);

            $this->getLogger()->log(sprintf('Found quality "%s" for url "%s"', $quality, $stream));

            if ($quality > $hqStream['quality']) {
                $hqStream['quality'] = $quality;
                $hqStream['url'] = $stream;
            }
        }

        $this->getLogger()->log(sprintf(
            'Best url is "%s" with quality of "%s"',
            $hqStream['url'],
            $hqStream['quality']
        ));

        return $hqStream['url'];
    }

    /**
     * @param string $stream
     *
     * @return string|null
     */
    protected function getQualityFromStreamUrl($stream)
    {
        return $this->getTools()->pregMatchDefault('#\/(\d+)-[\d_]+\.mp4#i', $stream, 0);
    }

    /**
     * @param object $widget
     * @param Result $result
     *
     * @return Result
     */
    protected function addTitle($widget, Result $result)
    {
        $result->setTitle($widget->show->title);
        $result->setEpisodeTitle($widget->title);

        return $result;
    }
}
