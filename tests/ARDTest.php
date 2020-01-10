<?php

namespace Tests;

use TheiNaD\DSMediatheken\Mediatheken\ARD;
use TheiNaD\DSMediatheken\Utils\Curl;
use TheiNaD\DSMediatheken\Utils\Logger;
use TheiNaD\DSMediatheken\Utils\Result;
use TheiNaD\DSMediatheken\Utils\Tools;

/**
 * Unit Test for ARD
 *
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2018-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
final class ARDTest extends TestCase
{
    public function testDownloadInfoCanBeRetrievedFromValidUrl(): void
    {
        $VALID_DOWNLOAD_URL =
            'https://www.ardmediathek.de/daserste/player/' .
            'Y3JpZDovL2Rhc2Vyc3RlLmRlL3RhdG9ydC85NDE0YjI0Mi04NjAwLTRjNmItOWRmZC1jM2Y1M2VkYTg1YTE/';
        $API_URL = 'http://www.ardmediathek.de/play/media/58459694';
        $MEDIA_FILE_URL =
            'https://pdvideosdaserste-a.akamaihd.net/int/2018/12/05/9414b242-8600-4c6b-9dfd-c3f53eda85a1/1280-1.mp4';

        $logger = $this->createMock(Logger::class);
        $curl = $this->createMock(Curl::class);
        $tools = new Tools($logger, $curl);

        $curl->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                [$this->equalTo($VALID_DOWNLOAD_URL)],
                [$this->equalTo($API_URL)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getFixture('ard/videoPage.html'),
                $this->getFixture('ard/apiResponse.json')
            );

        $ard = new ARD($logger, $tools);
        $result = $ard->getDownloadInfo($VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Tatort', $result->getTitle());
        $this->assertEquals('Vom Himmel hoch', $result->getEpisodeTitle());
    }

    public function testDownloadInfoCanBeRetrievedFromValidUrlWhereResultHasMissingProtocol(): void
    {
        $VALID_DOWNLOAD_URL =
            'https://www.ardmediathek.de/wdr/player/' .
            'Y3JpZDovL3dkci5kZS9CZWl0cmFnLWYwYzQ5MDliLWZiNzYtNDc4NS04Yzg5LWFlY2NhMWQ1YjU4Yw/wolfgang-bosbach';
        $API_URL = 'http://www.ardmediathek.de/play/media/58546868';
        $MEDIA_FILE_URL =
            'https://wdrmedien-a.akamaihd.net/medp/ondemand/weltweit/fsk0/179/1797623/1797623_21039278.mp4';

        $logger = $this->createMock(Logger::class);
        $curl = $this->createMock(Curl::class);
        $tools = new Tools($logger, $curl);

        $curl->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                [$this->equalTo($VALID_DOWNLOAD_URL)],
                [$this->equalTo($API_URL)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getFixture('ard/missingProtocol/videoPage.html'),
                $this->getFixture('ard/missingProtocol/apiResponse.json')
            );

        $ard = new ARD($logger, $tools);
        $result = $ard->getDownloadInfo($VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('die story', $result->getTitle());
        $this->assertEquals('Wolfgang Bosbach - vom Loslassen eines Gefesselten', $result->getEpisodeTitle());
    }

    public function testDownloadInfoCanBeRetrievedFromValidUrlContainingDocumentId(): void
    {
        $VALID_DOWNLOAD_URL =
            'http://mediathek.daserste.de/Die-Sendung-mit-der-Maus/MausSpezial-Frankreich-Maus/' .
            'Video?bcastId=1458&documentId=61013370';
        $API_URL = 'http://www.ardmediathek.de/play/media/61013370';
        $MEDIA_FILE_URL =
            'http://wdrmedien-a.akamaihd.net/medp/ondemand/weltweit/fsk0/187/1872647/1872647_21969608.mp4';

        $logger = $this->createMock(Logger::class);
        $curl = $this->createMock(Curl::class);
        $tools = new Tools($logger, $curl);

        $curl->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                [$this->equalTo($VALID_DOWNLOAD_URL)],
                [$this->equalTo($API_URL)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getFixture('ard/docIdInUrl/videoPage.html'),
                $this->getFixture('ard/docIdInUrl/apiResponse.json')
            );

        $ard = new ARD($logger, $tools);
        $result = $ard->getDownloadInfo($VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Die Sendung mit der Maus', $result->getTitle());
        $this->assertEquals('MausSpezial: Frankreich-Maus', $result->getEpisodeTitle());
    }
}
