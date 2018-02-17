<?php

require_once dirname(__FILE__) . '/../utils/Mediathek.php';
require_once dirname(__FILE__) . '/../utils/Result.php';

/**
 * @author Daniel Gehn <me@theinad.com>
 * @copyright 2018 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class Arte extends Mediathek
{

    protected static $LANGUAGE_MAP = array(
        'de' => 'de',
        'fr' => 'fr',
    );
    protected static $LANGUAGE_MAP_SHORT_LIBELLE = array(
        'de' => 'de',
        'fr' => ['vf', 'vof'],
    );
    protected static $OV_SHORT_LIBELLE = array(
        'og',
        'ov',
        'omu',
        'vostf'
    );

    protected $supportMatcher = 'arte.tv';

    protected $language = 'de';
    protected $languageShortLibelle = 'de';

    protected $platform = null;
    protected $detectedLanguage = null;
    protected $subdomain = null;

    public function getDownloadInfo($url, $username = '', $password = '')
    {
        $this->getLogger()->log('determining website and language by url ' . $url);
        if ($this->extractLanguageAndPlatformFromUrl($url) === false) {
            return null;
        }

        $this->changeLanguageIfDetected();

        $this->getLogger()->log('using language ' . $this->language . ' (' . print_r($this->languageShortLibelle, true) . ').');

        $this->getLogger()->log('fetching page content.');

        $videoPage = $this->getTools()->curlRequest($url);
        if ($videoPage === null) {
            return null;
        }

        $json = $this->processVideoPage($videoPage);
        if ($json === null) {
            return null;
        }

        $result = $this->getBestSource($json);
        if (!$result->hasUri()) {
            return null;
        }

        $result = $this->addTitle($result, $json);
        return $result;
    }

    protected function extractLanguageAndPlatformFromUrl($url)
    {
        if (preg_match('#https?:\/\/(\w+\.)?arte.tv\/(?:guide\/)?([a-zA-Z]+)#si', $url, $match) > 0) {
            $this->platform = 'arte';
            $this->subdomain = $match[1];
            $this->detectedLanguage = isset($match[2]) ? $match[2] : null;
            return true;
        }

        $this->getLogger()->log('not a known arte website');
        return false;
    }

    protected function changeLanguageIfDetected()
    {
        if ($this->detectedLanguage !== null &&
            isset(self::$LANGUAGE_MAP[$this->detectedLanguage]) &&
            isset(self::$LANGUAGE_MAP_SHORT_LIBELLE[$this->detectedLanguage])
        ) {
            $this->language = self::$LANGUAGE_MAP[$this->detectedLanguage];
            $this->languageShortLibelle = self::$LANGUAGE_MAP_SHORT_LIBELLE[$this->detectedLanguage];
        }
    }

    protected function processVideoPage($videoPage)
    {
        if (preg_match('#src=["|\']http.*?json_url=(.*?)%3F.*["|\']#si', $videoPage, $match) === 1) {
            $playerUrl = urldecode($match[1]);
            $this->getLogger()->log('the player is located at ' . $playerUrl);
            $json = $this->getTools()->curlRequest($playerUrl);
            if ($json === null) {
                return null;
            }

            return json_decode($json);
        }

        $this->getLogger()->log('could not identify player meta.');
        return null;
    }

    protected function getBestSource($json)
    {
        $result = new Result();

        foreach ($json->videoJsonPlayer->VSR as $source) {
            $this->getLogger()->log("found quality of $source->bitrate with language $source->versionLibelle ($source->versionShortLibelle)");
            $shortLibelleLowercase = mb_strtolower($source->versionShortLibelle);
            if ($source->mediaType == "mp4" &&
                (
                    $this->shortLibelleMatches($shortLibelleLowercase) ||
                    $this->shortLibelleIsOv($shortLibelleLowercase)
                ) &&
                $source->bitrate > $result->getBitrateRating()
            ) {
                $result = new Result();

                $result->setBitrateRating($source->bitrate);
                $result->setUri($source->url);
            }
        }

        return $result;
    }

    protected function shortLibelleMatches($shortLibelle)
    {
        if (!is_array($this->languageShortLibelle)) {
            return $shortLibelle === $this->languageShortLibelle;
        }
        
        return in_array($shortLibelle, $this->languageShortLibelle);
    }

    protected function shortLibelleIsOv($shortLibelle)
    {
        return in_array($shortLibelle, self::$OV_SHORT_LIBELLE);
    }

    protected function addTitle($result, $json)
    {
        $result->setTitle(trim($json->videoJsonPlayer->VTI));
        $result->setEpisodeTitle(isset($json->videoJsonPlayer->VSU) ? trim($json->videoJsonPlayer->VSU) : '');
        return $result;
    }
}
