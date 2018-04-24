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
class DreiSat extends Mediathek
{

    private static $DREISAT_XML_SERVICE_PATTERN = 'http://www.3sat.de/mediathek/xmlservice/web/beitragsDetails?ak=web&id={id}&ak=web';
  /**
   * Facets containing this type will be completely ignored.
   *
   * @var array
   */
    private static $UNSUPPORTED_FACETS = array(
    'hbbtv',
    'restriction_useragent',
    );
  /**
   * Maps Qualities to ratings.
   *
   * A higher rating means the quality is preferred.
   * A rating of -1 means the quality will be ignored.
   *
   * @var array
   */
    private static $QUALITY_RATING = array(
    'low' => 0,
    'med' => 1,
    'high' => 2,
    'veryhigh' => 3,
    );
    protected static $supportMatcher = '3sat.de';

    public function getDownloadInfo($url, $username = '', $password = '')
    {
        $objectId = $this->getObjectId($url);
        if ($objectId === null) {
            $this->getLogger()->log('Couldn\'t identify object id in ' . $url);
            return null;
        }

        $this->getLogger()->log('ID is ' . $objectId);
        $this->getLogger()->log('Getting XML data from ' . str_replace(
            '{id}',
            $objectId,
            self::$DREISAT_XML_SERVICE_PATTERN
        ));
        $rawXML =
        $this->getTools()->curlRequest(str_replace(
            '{id}',
            $objectId,
            self::$DREISAT_XML_SERVICE_PATTERN
        ));
        if ($rawXML === null) {
            $this->getLogger()->log('Failed to retrieve xml data.');
            return null;
        }

        return $this->processXML($rawXML);
    }

    private function getObjectId($url)
    {
        if (preg_match('#mediathek\/(?:.*)obj=(\d+)#i', $url, $match) === 1) {
            return $match[1];
        }
        return null;
    }

    protected function processXML($rawXML)
    {
        if ($this->isStatusOk($rawXML) === false) {
            $this->getLogger()->log('status not ok');
            return null;
        }

        $result = new Result();

        $matches = array();
        preg_match_all('#<formitaet basetype="(.*?)".*?>(.*?)</formitaet>#is', $rawXML, $matches);
        foreach ($matches[1] as $index => $basetype) {
            if (strpos($basetype, 'mp4_http') !== false) {
                $formitaet = $matches[2][$index];
                if ($this->isFacetSupported($formitaet) === false) {
                    continue;
                }

                $quality = $this->getQuality($formitaet);
                $bitrate = $this->getBitrate($formitaet);

                if ($quality >= $result->getQualityRating() && $bitrate > $result->getBitrateRating()) {
                    $result = new Result();
                    $result->setQualityRating($quality);
                    $result->setBitrateRating($bitrate);
                    $result->setUri($this->getUrl($formitaet));
                }
            }
        }

        if (!$result->hasUri()) {
            $this->getLogger()->log('No format found');
            return null;
        }

        $result->setTitle($this->getTitle($rawXML));
        $result->setEpisodeTitle($this->getEpisodeTitle($rawXML));
        $result->setUri($this->getTools()->addProtocolFromUrlIfMissing($result->getUri(), $url));

        return $result;
    }

    private function isStatusOk($rawXML)
    {
        $match = array();
        if (preg_match('#<statuscode>(.*?)</statuscode>#i', $rawXML, $match) == 1) {
            return $match[1] === 'ok';
        }
        return false;
    }

    private function isFacetSupported($formitaet)
    {
        $match = array();
        if (preg_match('#<facet>(.*?)</facet>#is', $formitaet, $match) == 1) {
            if (!in_array($match[1], self::$UNSUPPORTED_FACETS)) {
                return true;
            }
        }

        return false;
    }

    private function getQuality($formitaet)
    {
        $match = array();
        if (preg_match('#<quality>(.*?)</quality>#is', $formitaet, $match) == 1) {
            return self::$QUALITY_RATING[$match[1]];
        }

        return -1;
    }

    private function getBitrate($formitaet)
    {
        $match = array();
        if (preg_match('#<videoBitrate>(.*?)</videoBitrate>#is', $formitaet, $match) == 1) {
            return $match[1];
        }
        return -1;
    }

    private function getUrl($formitaet)
    {
        $match = array();
        if (preg_match('#<url>(.*?)</url>#is', $formitaet, $match) == 1) {
            return trim($match[1]);
        }
        return null;
    }

    private function getTitle($rawXML)
    {
        $match = array();
        if (preg_match('#<originChannelTitle>(.*?)<\/originChannelTitle>#i', $rawXML, $match) == 1) {
            return $match[1];
        }
        return null;
    }

    private function getEpisodeTitle($rawXML)
    {
        $match = array();
        if (preg_match('#<title>(.*?)<\/title>#i', $rawXML, $match) == 1) {
            return $match[1];
        }
        return null;
    }
}
