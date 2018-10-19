<?php
namespace TheiNaD\DSMediatheken\Mediatheken;

use TheiNaD\DSMediatheken\Utils\Result;
use TheiNaD\DSMediatheken\Utils\Mediathek;

require_once dirname(__FILE__) . '/../utils/Mediathek.php';
require_once dirname(__FILE__) . '/../utils/Result.php';

/**
 * @author Daniel Gehn <me@theinad.com>
 * @copyright 2018 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class NDR extends Mediathek
{
    protected static $supportMatcher = array('ndr.de');

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

    protected function getContentUrl($videoPage)
    {
        if (preg_match('#itemprop="contentUrl" content="(.*?)"#si', $videoPage, $match) === 1) {
            return $match[1];
        }
        return null;
    }

    protected function processSource($source, $result)
    {
        $result = new Result();
        $result->setUri($source);

        return $result;
    }

    protected function addTitles($result, $videoPage)
    {
        $result = $this->handleNamesFromVideoPage($result, $videoPage);
        $result = $this->handleEpisodeNumberAndDateFromVideoPage($result, $videoPage);
        return $result;
    }

    protected function handleNamesFromVideoPage($result, $videoPage)
    {
        if (preg_match('#itemprop="name" content="(.*?)"#is', $videoPage, $nameMatch) === 1) {
            $result->setTitle(str_replace('Video: ', '', $nameMatch[1]));

            if (preg_match('#itemprop="alternateName">(.*?)<\/#is', $videoPage, $alternateNameMatch) === 1) {
                $result->setEpisodeTitle($result->getTitle());
                $result->setTitle($alternateNameMatch[1]);
            }

            return $result;
        }

        if (preg_match('#itemprop="headline">(.*?)<\/#i', $videoPage, $headlineMatch) === 1) {
            $result->setTitle($headlineMatch[1]);
        }

        return $result;
    }

    protected function handleEpisodeNumberAndDateFromVideoPage($result, $videoPage)
    {
        $episodeTitle = $result->getEpisodeTitle();

        if (preg_match('#itemprop="episodeNumber">(.*?)<\/#is', $videoPage, $episodeNumber) === 1) {
            $episodeTitle = empty($episodeTitle) ? '' : $episodeTitle . ' ';
            $episodeTitle .= $episodeNumber[1];
        }

        if (preg_match('#itemprop="startDate" content="(.*?)">#is', $videoPage, $startDate) === 1) {
            $episodeTitle = empty($episodeTitle) ? '' : $episodeTitle . ' ';
            $episodeTitle .= 'vom ' . date('d.m.Y', strtotime($startDate[1]));
        }

        $result->setEpisodeTitle($episodeTitle);

        return $result;
    }
}
