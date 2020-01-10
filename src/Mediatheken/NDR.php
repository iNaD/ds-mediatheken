<?php

namespace TheiNaD\DSMediatheken\Mediatheken;

use TheiNaD\DSMediatheken\Utils\Mediathek;
use TheiNaD\DSMediatheken\Utils\Result;

/**
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2018-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
class NDR extends Mediathek
{
    protected static $SUPPORT_MATCHER = ['ndr.de'];

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

        $contentUrl = $this->getContentUrl($videoPage);
        if ($contentUrl === null) {
            $this->getLogger()->log('no contentUrl found at ' . $url);

            return null;
        }

        $result = $this->processSource($contentUrl, $result);
        if (!$result->hasUri()) {
            $this->getLogger()->log('no source found at ' . $url);

            return null;
        }

        $result = $this->addTitles($result, $videoPage);
        $result->setUri($this->getTools()->addProtocolFromUrlIfMissing($result->getUri(), $url));

        return $result;
    }

    /**
     * @param string $videoPage
     *
     * @return string|null
     */
    protected function getContentUrl($videoPage)
    {
        return $this->getTools()->pregMatchDefault('#itemprop="contentUrl" content="(.*?)"#si', $videoPage);
    }

    /**
     * @param string $source
     * @param Result $result
     *
     * @return Result
     */
    protected function processSource($source, $result)
    {
        $result->setUri($source);

        return $result;
    }

    /**
     * @param Result $result
     * @param string $videoPage
     *
     * @return Result
     */
    protected function addTitles($result, $videoPage)
    {
        $result = $this->handleNamesFromVideoPage($result, $videoPage);
        $result = $this->handleEpisodeNumberAndDateFromVideoPage($result, $videoPage);

        return $result;
    }

    /**
     * @param Result $result
     * @param string $videoPage
     *
     * @return Result
     */
    protected function handleNamesFromVideoPage($result, $videoPage)
    {
        $name = $this->getTools()->pregMatchDefault('#itemprop="name" content="(.*?)"#is', $videoPage);
        if ($name !== null) {
            $result->setTitle(str_replace('Video: ', '', $name));

            $alternateName = $this->getTools()->pregMatchDefault('#itemprop="alternateName">(.*?)<\/#is', $videoPage);
            if ($alternateName !== null) {
                $result->setEpisodeTitle($result->getTitle());
                $result->setTitle($alternateName);
            }
        } else {
            $headline = $this->getTools()->pregMatchDefault('#itemprop="headline">(.*?)<\/#i', $videoPage);
            if ($headline !== null) {
                $result->setTitle($headline);
            }
        }

        return $result;
    }

    /**
     * @param Result $result
     * @param string $videoPage
     *
     * @return Result
     */
    protected function handleEpisodeNumberAndDateFromVideoPage($result, $videoPage)
    {
        $episodeTitle = $result->getEpisodeTitle();

        $episodeNumber = $this->getTools()->pregMatchDefault('#itemprop="episodeNumber">(.*?)<\/#is', $videoPage);
        if ($episodeNumber !== null) {
            $episodeTitle = empty($episodeTitle) ? '' : $episodeTitle . ' ';
            $episodeTitle .= $episodeNumber;
        }

        $startDate = $this->getTools()->pregMatchDefault('#itemprop="startDate" content="(.*?)">#is', $videoPage);
        if ($startDate !== null) {
            $episodeTitle = empty($episodeTitle) ? '' : $episodeTitle . ' ';
            $episodeTitle .= 'vom ' . date('d.m.Y', strtotime($startDate));
        }

        $result->setEpisodeTitle($episodeTitle);

        return $result;
    }
}
