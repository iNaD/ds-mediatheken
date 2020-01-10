<?php

namespace TheiNaD\DSMediatheken\Utils;

use RuntimeException;

/**
 * Simple Curl wrapper to make things easier and mockable.
 *
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2018-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
class Curl
{
    public static $MOBILE_USERAGENT =
        'Mozilla/5.0 (Linux; Android 4.1; Galaxy Nexus Build/JRN84D)' .
        ' AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.166 Mobile Safari/535.19';

    /**
     * Send a curl request with given options an mobile useragent.
     *
     * @param string $url     url to be requested
     * @param array  $options modify curl options
     *
     * @return string
     *
     * @throws RuntimeException if the request failed
     */
    public function requestMobile($url, $options = [])
    {
        $options[CURLOPT_USERAGENT] = self::$MOBILE_USERAGENT;

        return $this->request($url, $options);
    }

    /**
     * Send a curl request with given options.
     *
     * @param string $url     url to be requested
     * @param array  $options modify curl options
     *
     * @return string
     *
     * @throws RuntimeException if the request failed
     */
    public function request($url, $options = [])
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);

        $this->setDefaults($curl);
        $this->applyOptions($curl, $options);

        $result = curl_exec($curl);
        if (!$result) {
            throw new RuntimeException(sprintf('Request failed (%s): %s', curl_errno($curl), curl_error($curl)));
        }

        curl_close($curl);

        return $result;
    }

    /**
     * @param resource $curl
     *
     * @noinspection CurlSslServerSpoofingInspection
     */
    protected function setDefaults($curl)
    {
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    }

    /**
     * @param resource $curl
     * @param array    $options
     */
    protected function applyOptions($curl, $options)
    {
        foreach ($options as $option => $value) {
            curl_setopt($curl, $option, $value);
        }
    }
}
