<?php

namespace TheiNaD\DSMediatheken\Utils;

/**
 * Represents a result of a Mediathek
 *
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2017-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
class Result
{
    /** @var int */
    private $mimeTypeRating = -1;

    /** @var int */
    private $qualityRating = -1;

    /** @var int */
    private $bitrateRating = -1;

    /** @var string|null */
    private $uri;

    /** @var string */
    private $title = '';

    /** @var string */
    private $episodeTitle = '';

    /**
     * @return int
     */
    public function getMimeTypeRating()
    {
        return $this->mimeTypeRating;
    }

    /**
     * @param int $mimeTypeRating
     */
    public function setMimeTypeRating($mimeTypeRating)
    {
        $this->mimeTypeRating = $mimeTypeRating;
    }

    /**
     * @return int
     */
    public function getQualityRating()
    {
        return $this->qualityRating;
    }

    /**
     * @param int $qualityRating
     */
    public function setQualityRating($qualityRating)
    {
        $this->qualityRating = $qualityRating;
    }

    /**
     * @return int
     */
    public function getBitrateRating()
    {
        return $this->bitrateRating;
    }

    /**
     * @param int $bitrateRating
     */
    public function setBitrateRating($bitrateRating)
    {
        $this->bitrateRating = $bitrateRating;
    }

    /**
     * @return string|null
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string|null $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @return bool
     */
    public function hasUri()
    {
        return $this->uri !== null;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getEpisodeTitle()
    {
        return $this->episodeTitle;
    }

    /**
     * @param string $episodeTitle
     */
    public function setEpisodeTitle($episodeTitle)
    {
        $this->episodeTitle = $episodeTitle;
    }
}
