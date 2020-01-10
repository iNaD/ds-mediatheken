<?php

namespace TheiNaD\DSMediatheken\Mediatheken;

use TheiNaD\DSMediatheken\Utils\Mediathek;
use TheiNaD\DSMediatheken\Utils\Result;

/**
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2017-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
class KiKa extends Mediathek
{
    const PROGRESSIVE_DOWNLOAD_PATTERN = '#<progressiveDownloadUrl>(.*?)<\/progressiveDownloadUrl>#si';

    protected static $SUPPORT_MATCHER = ['kika.de'];

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

    /**
     * @param string $videoPage
     *
     * @return string|null
     */
    protected function getApiUrl($videoPage)
    {
        return $this->getTools()->pregMatchDefault('#dataURL:\'(.*?)\'#si', $videoPage);
    }

    /**
     * @param string $apiData
     *
     * @return array
     */
    protected function getSources($apiData)
    {
        return $this->getTools()->pregMatchAllDefault('#<asset>(.*?)<\/asset>#si', $apiData);
    }

    /**
     * @param string $source
     * @param Result $result
     *
     * @return Result
     */
    protected function processSource($source, $result)
    {
        // source has needed download url
        $downloadUrl = $this->getTools()->pregMatchDefault(self::PROGRESSIVE_DOWNLOAD_PATTERN, $source);
        if ($downloadUrl === null) {
            return $result;
        }

        if (strpos($downloadUrl, '.mp4') !== false) {
            // we need a bitrate to find the best quality
            $bitrateVideo = $this->getTools()->pregMatchDefault('#<bitrateVideo>(.*?)<\/bitrateVideo>#si', $source);
            if ($bitrateVideo === null) {
                return $result;
            }

            if ($result->getBitrateRating() < $bitrateVideo) {
                $result = new Result();
                $result->setBitrateRating($bitrateVideo);
                $result->setUri($downloadUrl);
            }
        }

        return $result;
    }

    /**
     * @param Result $result
     * @param string $apiData
     *
     * @return mixed
     */
    protected function addTitle($result, $apiData)
    {
        $result->setTitle($this->getTitleFromApiData($apiData));
        $result->setEpisodeTitle($this->getEpisodeTitleFromApiData($apiData));

        return $result;
    }

    /**
     * @param string $apiData
     *
     * @return string|null
     */
    protected function getTitleFromApiData($apiData)
    {
        return $this->getTools()->pregMatchDefault('#<topline>(.*?)<\/topline>#i', $apiData);
    }

    /**
     * @param string $apiData
     *
     * @return string|null
     */
    protected function getEpisodeTitleFromApiData($apiData)
    {
        return $this->getTools()->pregMatchDefault('#<title>(.*?)<\/title>#i', $apiData);
    }
}
