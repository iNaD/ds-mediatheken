<?php
namespace Tests;

use TheiNaD\DSMediatheken\Utils\Curl;
use TheiNaD\DSMediatheken\Utils\Tools;
use TheiNaD\DSMediatheken\Utils\Logger;
use TheiNaD\DSMediatheken\Utils\Result;
use TheiNaD\DSMediatheken\Mediatheken\ARD;

/**
 * Unit Test for ARD
 *
 * @author Daniel Gehn <me@theinad.com>
 * @copyright 2018 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
final class ARDTest extends TestCase
{
    public function testDownloadInfoCanBeRetrievedFromValidUrl(): void
    {
        $VALID_DOWNLOAD_URL = 'http://www.ardmediathek.de/tv/Filme-im-Ersten/St-Josef-am-Berg-Berge-auf-Probe/Das-Erste/Video?bcastId=1933898&documentId=50077518';
        $API_URL = 'http://www.ardmediathek.de/play/media/50077518';
        $MEDIA_FILE_URL = 'https://pdvideosdaserste-a.akamaihd.net/de/2018/02/07/9e39ab47-315e-44fc-b61b-c5b386d41c00/960-1.mp4';

        $logger = $this->createMock(Logger::class);
        $curl = $this->createMock(Curl::class);
        $tools = new Tools($logger, $curl);

        $curl->expects($this->exactly(2))
        ->method('request')
        ->withConsecutive(
            [$this->equalTo($API_URL)],
            [$this->equalTo($VALID_DOWNLOAD_URL)]
        )
        ->willReturnOnConsecutiveCalls(
            $this->getFixture('ard/apiResponse.json'),
            $this->getFixture('ard/videoPage.html')
        );

        $ard = new ARD($logger, $tools);
        $result = $ard->getDownloadInfo($VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Filme im Ersten', $result->getTitle());
        $this->assertEquals('St. Josef am Berg - Berge auf Probe', $result->getEpisodeTitle());
    }

    public function testDownloadInfoCanBeRetrievedFromValidUrlWhereResultHasMissingProtocol(): void
    {
        $VALID_DOWNLOAD_URL = 'http://www.ardmediathek.de/tv/Doku-am-Freitag/Meine-Br%C3%BCder-und-Schwestern-in-Nordkorea/WDR-Fernsehen/Video?bcastId=12877116&documentId=50018574';
        $API_URL = 'http://www.ardmediathek.de/play/media/50018574';
        $MEDIA_FILE_URL = 'http://wdrmedien-a.akamaihd.net/medp/ondemand/weltweit/fsk0/157/1573923/1573923_18098334.mp4';

        $logger = $this->createMock(Logger::class);
        $curl = $this->createMock(Curl::class);
        $tools = new Tools($logger, $curl);

        $curl->expects($this->exactly(2))
        ->method('request')
        ->withConsecutive(
            [$this->equalTo($API_URL)],
            [$this->equalTo($VALID_DOWNLOAD_URL)]
        )
        ->willReturnOnConsecutiveCalls(
            $this->getFixture('ard/missingProtocol/apiResponse.json'),
            $this->getFixture('ard/missingProtocol/videoPage.html')
        );

        $ard = new ARD($logger, $tools);
        $result = $ard->getDownloadInfo($VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Doku am Freitag', $result->getTitle());
        $this->assertEquals('Meine Brüder und Schwestern in Nordkorea', $result->getEpisodeTitle());
    }
}
