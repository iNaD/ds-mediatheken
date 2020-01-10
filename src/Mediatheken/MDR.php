<?php

namespace TheiNaD\DSMediatheken\Mediatheken;

/**
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2017-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
class MDR extends KiKa
{
    protected static $SUPPORT_MATCHER = ['mdr.de'];
    protected static $apiBaseUrl = 'https://www.mdr.de';

    protected function getApiUrl($videoPage)
    {
        $apiEndpoint = $this->getTools()->pregMatchDefault('#\'playerXml\':\'(.*?)\'#si', $videoPage);
        if ($apiEndpoint === null) {
            return null;
        }

        return static::$apiBaseUrl . $this->cleanupApiUrl($apiEndpoint);
    }

    /**
     * @param string $apiData
     *
     * @return string|null
     */
    protected function getTitleFromApiData($apiData)
    {
        return $this->getTools()->pregMatchDefault('#<broadcastSeriesName>(.*?)<\/broadcastSeriesName>#i', $apiData);
    }

    /**
     * @param string $apiUrl
     *
     * @return string
     */
    protected function cleanupApiUrl($apiUrl)
    {
        return str_replace('\\/', '/', $apiUrl);
    }
}
