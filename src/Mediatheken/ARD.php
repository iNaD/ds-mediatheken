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
    protected static $API_BASE_URL = 'http://www.ardmediathek.de/play/media/';
    protected static $VALID_QUALITIES = [0, 1, 2, 3, 4];
    protected static $SUPPORT_MATCHER = ['ardmediathek.de', 'mediathek.daserste.de'];

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

        $pageContent = $this->getPageContent($url);
        if ($pageContent === null) {
            $this->getLogger()->log(sprintf('could not retrieve page content from %s', $url));

            return null;
        }

        $documentId = $this->getDocumentIdFromUrl($url);
        if ($documentId === null) {
            $documentId = $this->getDocumentId($pageContent);
        }

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

        if (!$result->hasUri()) {
            return null;
        }

        $this->getLogger()->log(sprintf(
            'Url "%s" won with quality of "%s"',
            $result->getUri(),
            $result->getQualityRating()
        ));

        $result = $this->addTitle($pageContent, $result);
        $result->setUri($this->getTools()->addProtocolFromUrlIfMissing($result->getUri(), $url));

        return $result;
    }

    /**
     * @param string $url
     *
     * @return string|null
     */
    protected function getPageContent($url)
    {
        return $this->getTools()->curlRequest($url);
    }

    /**
     * @param string $url
     *
     * @return string|null
     */
    protected function getDocumentIdFromUrl($url)
    {
        return $this->getTools()->pregMatchDefault('#documentId=([0-9]+)#i', $url, null);
    }

    /**
     * @param string $pageContent
     *
     * @return string|null
     */
    protected function getDocumentId($pageContent)
    {
        return $this->getTools()->pregMatchDefault('#"contentId":([0-9]+)#i', $pageContent, null);
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
     * @param string $pageContent
     * @param Result $result
     *
     * @return Result
     */
    protected function addTitle($pageContent, Result $result)
    {
        $videoMeta = $this->getVideoMeta($pageContent);
        if ($videoMeta !== null) {
            return $this->addTitleFromVideoMeta($result, $videoMeta);
        }

        $titleTag = $this->getTools()->pregMatchDefault('#<title>(.*?)<\/title>#i', $pageContent, null);
        if ($titleTag === null) {
            return $result;
        }

        $splitted = explode('|', $titleTag);
        $episode = trim($splitted[0]);
        $title = isset($splitted[1]) ? trim($splitted[1]) : null;

        if ($title === null) {
            $result->setTitle($episode);

            return $result;
        }

        $title = str_replace('Video zu ', '', $title);

        $result->setTitle($title);
        $result->setEpisodeTitle($episode);

        return $result;
    }

    /**
     * @param Result $result
     * @param string $videoMeta
     *
     * @return Result
     */
    protected function addTitleFromVideoMeta(Result $result, $videoMeta)
    {
        $show = $this->getShowFromMeta($videoMeta);
        $clipTitle = $this->getClipTitleFromMeta($videoMeta);

        $result->setTitle(trim($show));
        $result->setEpisodeTitle(trim($clipTitle));

        return $result;
    }

    /**
     * @param string $pageContent
     *
     * @return string|null
     */
    protected function getVideoMeta($pageContent)
    {
        $scriptTags = $this->getTools()->pregMatchAllDefault(
            '#<script type="text\/javascript">(.*?)<\/script>#si',
            $pageContent
        );

        if (count($scriptTags) === 0) {
            return null;
        }

        foreach ($scriptTags as $scriptTag) {
            $atiCustomVars = $this->getTools()->pregMatchDefault('#tracking\.atiCustomVars":{(.*?)}#si', $scriptTag);
            if ($atiCustomVars === null) {
                continue;
            }

            return $atiCustomVars;
        }

        return null;
    }

    /**
     * @param string $videoMeta
     *
     * @return string|null
     */
    protected function getShowFromMeta($videoMeta)
    {
        return $this->getTools()->pregMatchDefault('#"show":"(.*?)"#i', $videoMeta, null);
    }

    /**
     * @param string $videoMeta
     *
     * @return string|null
     */
    protected function getClipTitleFromMeta($videoMeta)
    {
        return $this->getTools()->pregMatchDefault('#"clipTitle":"(.*?)"#i', $videoMeta, null);
    }
}
