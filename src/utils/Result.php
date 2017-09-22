<?php

/**
 * Represents a result of a Mediathek
 *
 * @author Daniel Gehn <me@theinad.com>
 * @version 0.0.1
 * @copyright 2017 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class Result {
  private $mimeTypeRating = -1;
  private $qualityRating = -1;
  private $uri = null;
  private $title = '';
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
   * @return null
   */
  public function getUri()
  {
    return $this->uri;
  }

  /**
   * @param null $uri
   */
  public function setUri($uri)
  {
    $this->uri = $uri;
  }

  public function hasUri() {
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
