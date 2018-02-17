<?php

final class ARDTest extends TestCase
{
  protected static $VALID_DOWNLOAD_URL = 'http://www.ardmediathek.de/tv/Filme-im-Ersten/St-Josef-am-Berg-Berge-auf-Probe/Das-Erste/Video?bcastId=1933898&documentId=50077518';
  protected static $API_URL = 'http://www.ardmediathek.de/play/media/50077518';
  protected static $MEDIA_FILE_URL = 'https://pdvideosdaserste-a.akamaihd.net/de/2018/02/07/9e39ab47-315e-44fc-b61b-c5b386d41c00/960-1.mp4';

  public function testDownloadInfoCanBeRetrievedFromValidUrl(): void
  {
    $logger = $this->createMock(Logger::class);
    $curl = $this->createMock(Curl::class);
    $tools = new Tools($logger, $curl);

    $curl->expects($this->exactly(2))
      ->method('request')
      ->withConsecutive(
        [$this->equalTo(self::$API_URL)],
        [$this->equalTo(self::$VALID_DOWNLOAD_URL)]
      )
      ->willReturnOnConsecutiveCalls(
        $this->getFixture('ard/apiResponse.json'),
        $this->getFixture('ard/videoPage.html')
      );

    $ard = new ARD($logger, $tools);
    $result = $ard->getDownloadInfo(self::$VALID_DOWNLOAD_URL);

    $this->assertInstanceOf(Result::class, $result);
    $this->assertEquals(self::$MEDIA_FILE_URL, $result->getUri());
    $this->assertEquals('Filme im Ersten', $result->getTitle());
    $this->assertEquals('St. Josef am Berg - Berge auf Probe', $result->getEpisodeTitle());
  }
}
