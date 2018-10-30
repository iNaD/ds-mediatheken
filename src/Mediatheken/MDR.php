<?php

namespace TheiNaD\DSMediatheken\Mediatheken;

use TheiNaD\DSMediatheken\Utils\Mediathek;
use TheiNaD\DSMediatheken\Utils\Result;

/**
 * @author Daniel Gehn <me@theinad.com>
 * @copyright 2017-2018 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class MDR extends Mediathek
{
    protected static $supportMatcher = ['mdr.de'];

    private static $apiBaseUrl = 'https://www.mdr.de';

    public function getDownloadInfo($url, $username = '', $password = '')
    {
        $result = new Result();

        $videoPage = $this->getTools()->curlRequest($url);
        $apiUrl = $this->getApiUrl($videoPage);
        if ($apiUrl === null) {
            $this->getLogger()->log('no apiUrl found at ' . $url);
            return null;
        }

        $apiData = $this->getTools()->curlRequest($apiUrl);
        if ($apiData === null) {
            $this->getLogger()->log('could not retrieve apiData');
            return null;
        }

        $sources = $this->getSources($apiData);
        foreach ($sources as $source) {
            $result = $this->processSource($source, $result);
        }

        if (!$result->hasUri()) {
            return null;
        }

        $result = $this->addTitle($result, $apiData);
        $result->setUri($this->getTools()->addProtocolFromUrlIfMissing($result->getUri(), $url));

        return $result;
    }

    protected function getApiUrl($videoPage)
    {
        if (preg_match('#\'playerXml\':\'(.*?)\'#si', $videoPage, $match) === 1) {
            return static::$apiBaseUrl . $this->cleanupApiUrl($match[1]);
        }
        return null;
    }

    protected function getSources($apiData)
    {
        preg_match_all('#<asset>(.*?)<\/asset>#si', $apiData, $matches);
        return $matches[1];
    }

    protected function processSource($source, Result $result)
    {
        // source has needed download url
        if (preg_match("#<progressiveDownloadUrl>(.*?)<\/progressiveDownloadUrl>#si", $source, $downloadUrl) !== 1) {
            return $result;
        }

        $url = $downloadUrl[1];
        if (strpos($url, '.mp4') !== false) {
            // we need a bitrate to find the best quality
            if (preg_match("#<bitrateVideo>(.*?)<\/bitrateVideo>#si", $source, $bitrateVideo) !== 1) {
                return $result;
            }

            $bitrate = $bitrateVideo[1];
            if ($result->getBitrateRating() < $bitrate) {
                $result = new Result();
                $result->setBitrateRating($bitrate);
                $result->setUri($url);
            }
        }

        return $result;
    }

    protected function addTitle(Result $result, $apiData)
    {
        $result->setTitle($this->getTitleFromApiData($apiData));
        $result->setEpisodeTitle($this->getEpisodeTitleFromApiData($apiData));
        return $result;
    }

    protected function getTitleFromApiData($apiData)
    {
        if (preg_match('#<broadcastSeriesName>(.*?)<\/broadcastSeriesName>#i', $apiData, $match) !== 1) {
            return null;
        }
        return $match[1];
    }

    protected function getEpisodeTitleFromApiData($apiData)
    {
        if (preg_match('#<title>(.*?)<\/title>#i', $apiData, $match) !== 1) {
            return null;
        }
        return $match[1];
    }

    private function cleanupApiUrl($apiUrl)
    {
        return str_replace('\\/', '/', $apiUrl);
    }
}
