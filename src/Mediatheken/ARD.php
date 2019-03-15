<?php

namespace TheiNaD\DSMediatheken\Mediatheken;

use TheiNaD\DSMediatheken\Utils\Mediathek;
use TheiNaD\DSMediatheken\Utils\Result;

/**
 * @author Daniel Gehn <me@theinad.com>
 * @copyright 2017-2019 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class ARD extends Mediathek
{

    private static $API_BASE_URL = 'http://www.ardmediathek.de/play/media/';
    private static $VALID_QUALITIES = [0, 1, 2, 3, 4];

    protected static $supportMatcher = ['ardmediathek.de', 'mediathek.daserste.de'];

    public function getDownloadInfo($url, $username = '', $password = '')
    {
        $result = new Result();

        $pageContent = $this->getPageContent($url);

        if ($pageContent === null) {
            $this->getLogger()->log(sprintf('could not retrieve page content from %s', $url));
            return null;
        }

        $documentId = $this->getDocumentId($pageContent);
        if ($documentId === null) {
            $this->getLogger()->log('no documentId found in ' . $url);
            return null;
        }

        $apiData = $this->getApiData($documentId);
        if ($apiData === null) {
            $this->getLogger()->log('could not retrieve apiData');
            return null;
        }

        foreach ($apiData->_mediaArray as $media) {
            foreach ($media->_mediaStreamArray as $mediaStream) {
                if ($this->mediaStreamHasNeededProperties($mediaStream)
                    && $this->mediaStreamHasValidQuality($mediaStream)) {
                    if ($mediaStream->_quality > $result->getQualityRating()) {
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

        if (!$result->hasUri()) {
            return null;
        }

        $result = $this->addTitle($pageContent, $result);
        $result->setUri($this->getTools()->addProtocolFromUrlIfMissing($result->getUri(), $url));

        return $result;
    }

    private function getPageContent($url)
    {
        return $this->getTools()->curlRequest($url);
    }

    private function getDocumentId($pageContent)
    {
        if (preg_match('#"contentId":([0-9]+)#i', $pageContent, $match) !== 1) {
            return null;
        }

        return $match[1];
    }

    private function getApiData($documentId)
    {
        return json_decode($this->getTools()->curlRequest(self::$API_BASE_URL . $documentId));
    }

    private function mediaStreamHasNeededProperties($mediaStream)
    {
        return property_exists($mediaStream, '_stream') && property_exists($mediaStream, '_quality');
    }

    private function mediaStreamHasValidQuality($mediaStream)
    {
        return in_array($mediaStream->_quality, self::$VALID_QUALITIES, true);
    }

    private function getHighestQualityStream($streams)
    {
        if (!is_array($streams)) {
            return $streams;
        }

        $hqStream = [
            'quality' => 0,
            'url' => null,
        ];

        foreach ($streams as $stream) {
            $quality = $this->getQualityFromStreamUrl($stream);
            if ($quality > $hqStream['quality']) {
                $hqStream['quality'] = $quality;
                $hqStream['url'] = $stream;
            }
        }

        return $hqStream['url'];
    }

    private function getQualityFromStreamUrl($stream)
    {
        return $this->getTools()->pregMatchDefault('#\/(\d+)-\d.mp4#i', $stream, 0);
    }

    private function addTitle($pageContent, Result $result)
    {
        $videoMeta = $this->getVideoMeta($pageContent);

        if ($videoMeta === null) {
            return $result;
        }

        $show = $this->getShowFromMeta($videoMeta);
        $clipTitle = $this->getClipTitleFromMeta($videoMeta);

        $result->setTitle(trim($show));
        $result->setEpisodeTitle(trim($clipTitle));

        return $result;
    }

    private function getVideoMeta($pageContent)
    {
        \preg_match_all('#<script>(.*?)<\/script>#si', $pageContent, $scriptTags);

        if (count($scriptTags) === 0) {
            return null;
        }

        foreach ($scriptTags[1] as $scriptTag) {
            if (\preg_match('#tracking\.atiCustomVars":{(.*?)}#si', $scriptTag, $match) !== 1) {
                continue;
            }

            return $match[1];
        }

        return null;
    }

    private function getShowFromMeta($videoMeta)
    {
        if (preg_match('#"show":"(.*?)"#i', $videoMeta, $match) !== 1) {
            return null;
        }

        return $match[1];
    }

    private function getClipTitleFromMeta($videoMeta)
    {
        if (preg_match('#"clipTitle":"(.*?)"#i', $videoMeta, $match) !== 1) {
            return null;
        }

        return $match[1];
    }
}
