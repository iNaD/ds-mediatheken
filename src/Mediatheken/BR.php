<?php

namespace TheiNaD\DSMediatheken\Mediatheken;

use TheiNaD\DSMediatheken\Utils\Mediathek;
use TheiNaD\DSMediatheken\Utils\Result;

/**
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2017-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
class BR extends Mediathek
{
    const RELAY_BOOTSTRAP_DATA_PATTERN = '#window\.__RELAY_BOOTSTRAP_DATA__ = (.*?);#i';
    const ALLOWED_MIMETYPES = ['video/mp4'];

    protected static $SUPPORT_MATCHER = 'br.de';
    protected static $API_BASE_URL = 'https://api.mediathek.br.de';
    protected static $RELAY_BATCH = '/graphql/relayBatch';

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

        $videoPage = $this->getTools()->curlRequest($url);
        if ($videoPage === null) {
            $this->getLogger()->log('Video Page (' . $url . ') using login data (' . $username . '@'
                . $password . ') could not be loaded.');

            return null;
        }

        $metaData = $this->extractMetadata($videoPage);
        if ($metaData === null) {
            $this->getLogger()->log(sprintf('Failed to extract bootstrap json from url "%s"', $url));

            return null;
        }

        $videoId = $metaData->data->node->id;

        $detailPageRendererQuery = $this->detailPageRendererQuery($videoId);
        if ($detailPageRendererQuery === null) {
            $this->getLogger()->log('Failed to retrieve video data from graphql');

            return null;
        }

        $videoFiles = $detailPageRendererQuery->data->video->videoFiles->edges;
        foreach ($videoFiles as $videoFile) {
            $result = $this->processVideoFile($videoFile->node, $result);
        }

        $result->setTitle($metaData->data->node->kicker);
        $result->setEpisodeTitle($metaData->data->node->title);

        return $result;
    }

    /**
     * @param string $videoPage
     *
     * @return object|null
     */
    protected function extractMetadata($videoPage)
    {
        $bootstrapData = $this->getTools()->pregMatchDefault(self::RELAY_BOOTSTRAP_DATA_PATTERN, $videoPage);
        if ($bootstrapData === null) {
            return null;
        }

        $bootstrapJson = json_decode($bootstrapData, false);

        return $bootstrapJson[0][1];
    }

    /**
     * @param string $videoId
     *
     * @return object|null
     */
    protected function detailPageRendererQuery($videoId)
    {
        $jsonData = [
            [
                'id' => 'DetailPageRendererQuery',
                'query' => $this->getTools()->readGraphqlQuery('br.graphql'),
                'variables' => [
                    'clipId' => $videoId,
                ],
            ],
        ];
        $curlResponse = $this->getTools()->curlRequest(static::$API_BASE_URL . static::$RELAY_BATCH, [
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
        if (!in_array($videoFile->mimetype, static::ALLOWED_MIMETYPES, true)) {
            return $result;
        }

        $qualityRating = $videoFile->videoProfile->width * $videoFile->videoProfile->height;
        if ($qualityRating <= $result->getQualityRating()) {
            return $result;
        }

        $result->setUri($videoFile->publicLocation);
        $result->setQualityRating($qualityRating);

        return $result;
    }
}
