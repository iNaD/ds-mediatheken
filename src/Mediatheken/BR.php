<?php

namespace TheiNaD\DSMediatheken\Mediatheken;

use TheiNaD\DSMediatheken\Utils\Mediathek;
use TheiNaD\DSMediatheken\Utils\Result;

/**
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2017-2022 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
class BR extends Mediathek
{
    protected static $SUPPORT_MATCHER = 'br.de';
    protected static $API_URL = 'https://api.mediathek.br.de/graphql';

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

        $videoId = $this->getTools()->pregMatchDefault('#(av:[a-zA-Z0-9]+)#i', $url);
        if ($videoId === null) {
            $this->getLogger()->log('Could not extract videoId from url ' . $url);

            return null;
        }

        $detailPageQuery = $this->detailPageQuery($videoId);
        if ($detailPageQuery === null) {
            $this->getLogger()->log('Failed to retrieve video data from graphql');

            return null;
        }

        $videoFiles = $detailPageQuery->data->video->videoFiles->edges;
        foreach ($videoFiles as $videoFile) {
            $result = $this->processVideoFile($videoFile->node, $result);
        }

        $result->setTitle($detailPageQuery->data->video->kicker);
        $result->setEpisodeTitle($detailPageQuery->data->video->title);

        return $result;
    }

    /**
     * @param string $videoId
     *
     * @return object|null
     */
    protected function detailPageQuery($videoId)
    {
        $jsonData = [
            [
                'operationName' => 'detailPageQuery',
                'extensions' => [
                    'persistedQuery' => [
                        'version' => 1,
                        'sha256Hash' => '2d9f3248ef298727025898a384ae1d56d0884883048cc8e1b6df0b1711150ef8',
                    ],
                ],
                'variables' => [
                    'clipId' => $videoId,
                ],
            ],
        ];

        $curlResponse = $this->getTools()->curlRequest(static::$API_URL, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($jsonData),
        ]);

        if ($curlResponse === null) {
            return null;
        }

        $json = json_decode($curlResponse, false);

        return $json[0];
    }

    /**
     * @param object $videoFile
     * @param Result $result
     *
     * @return Result
     */
    protected function processVideoFile($videoFile, $result)
    {
        if ($videoFile->videoProfile->height === null) {
            return $result;
        }

        $qualityRating = $videoFile->videoProfile->height;
        if ($qualityRating <= $result->getQualityRating()) {
            return $result;
        }

        $result->setUri($videoFile->publicLocation);
        $result->setQualityRating($qualityRating);

        return $result;
    }
}
