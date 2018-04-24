<?php
namespace TheiNaD\DSMediatheken\Mediatheken;

use TheiNaD\DSMediatheken\Utils\Result;
use TheiNaD\DSMediatheken\Utils\Mediathek;

require_once dirname(__FILE__) . '/../utils/Mediathek.php';
require_once dirname(__FILE__) . '/../utils/Result.php';

/**
 * @author Daniel Gehn <me@theinad.com>
 * @copyright 2017-2018 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class ARD extends Mediathek
{

    private static $API_BASE_URL = 'http://www.ardmediathek.de/play/media/';
    private static $VALID_CDNS = array('default', 'akamai');
    private static $TITLE_PREFIX = 'Video zu ';
    private static $TITLE_SUFFIX = ' Video';

    protected static $supportMatcher = array('ardmediathek.de', 'mediathek.daserste.de');

    public function getDownloadInfo($url, $username = '', $password = '')
    {
        $result = new Result();

        $documentId = $this->getDocumentId($url);
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

    private function getDocumentId($url)
    {
        if (preg_match('#documentId=([0-9]+)#i', $url, $match) !== 1) {
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
        return property_exists($mediaStream, '_cdn') && property_exists($mediaStream, '_stream')
        && property_exists($mediaStream, '_quality');
    }

    private function mediaStreamHasValidCdn($mediaStream)
    {
        return in_array($mediaStream->_cdn, self::$VALID_CDNS);
    }

    private function addTitle($url, Result $result)
    {
        $html = $this->getTools()->curlRequest($url);

        $htmlTitle = $this->getTitleFromHtml($html);
        list($episodeTitle, $title) = explode(' | ', $htmlTitle);

        $result->setTitle($this->cleanupTitle($title));
        $result->setEpisodeTitle(trim($episodeTitle));

        return $result;
    }

    private function getTitleFromHtml($html)
    {
        if (preg_match('#<title>(.*?)<\/title>#i', $html, $match) !== 1) {
            return null;
        }
        return $match[1];
    }

    private function cleanupTitle($title)
    {
        $title = str_replace(self::$TITLE_PREFIX, '', $title);

        if ($this->getTools()->endsWith($title, self::$TITLE_SUFFIX)) {
            $title = substr($title, 0, strlen($title) - strlen(self::$TITLE_SUFFIX));
        }

        return trim($title);
    }
}
