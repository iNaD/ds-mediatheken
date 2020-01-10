<?php

namespace TheiNaD\DSMediatheken\Mediatheken;

use TheiNaD\DSMediatheken\Utils\Mediathek;
use TheiNaD\DSMediatheken\Utils\Result;

/**
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2017-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
class RBB extends Mediathek
{
    protected static $API_BASE_URL = 'http://mediathek.rbb-online.de/play/media/';
    protected static $VALID_CDNS = ['default', 'akamai'];
    protected static $SUPPORT_MATCHER = 'mediathek.rbb-online.de';

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

        $documentId = $this->getDocumentId($url);
        if ($documentId === null) {
            $this->getLogger()->log('No documentId found in ' . $url);

            return null;
        }

        $apiData = $this->getApiData($documentId);
        if ($apiData === null) {
            return null;
        }

        foreach ($apiData->_mediaArray as $media) {
            foreach ($media->_mediaStreamArray as $mediaStream) {
                if ($this->mediaStreamHasNeededProperties($mediaStream)
                    && $this->mediaStreamHasValidCdn($mediaStream)) {
                    if ($mediaStream->_quality > $result->getQualityRating()) {
                        $result = new Result();
                        $result->setQualityRating($mediaStream->_quality);
                        $result->setUri($mediaStream->_stream);
                    }
                }
            }
        }

        if (!$result->hasUri()) {
            return null;
        }

        $result = $this->addTitle($url, $result);
        $result->setUri($this->getTools()->addProtocolFromUrlIfMissing($result->getUri(), $url));

        return $result;
    }

    /**
     * @param string $url
     *
     * @return string|null
     */
    protected function getDocumentId($url)
    {
        return $this->getTools()->pregMatchDefault('#documentId=([0-9]+)#i', $url);
    }

    /**
     * @param string $documentId
     *
     * @return object
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
        return property_exists($mediaStream, '_cdn') && property_exists($mediaStream, '_stream')
            && property_exists($mediaStream, '_quality');
    }

    /**
     * @param object $mediaStream
     *
     * @return bool
     */
    protected function mediaStreamHasValidCdn($mediaStream)
    {
        return in_array($mediaStream->_cdn, self::$VALID_CDNS, true);
    }

    /**
     * @param string $url
     * @param Result $result
     *
     * @return Result
     */
    protected function addTitle($url, Result $result)
    {
        $html = $this->getTools()->curlRequest($url);
        $title = $this->getTitleFromHtml($html);
        $result->setTitle(trim($title));

        return $result;
    }

    /**
     * @param string $html
     *
     * @return string|null
     */
    protected function getTitleFromHtml($html)
    {
        return $this->getTools()->pregMatchDefault('#<h3 class="headline">(.*?)<\/h3>#i', $html);
    }
}
