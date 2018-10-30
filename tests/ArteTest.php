<?php

namespace Tests;

use TheiNaD\DSMediatheken\Mediatheken\Arte;
use TheiNaD\DSMediatheken\Utils\Curl;
use TheiNaD\DSMediatheken\Utils\Logger;
use TheiNaD\DSMediatheken\Utils\Result;
use TheiNaD\DSMediatheken\Utils\Tools;

/**
 * Unit Test for Arte
 *
 * @author Daniel Gehn <me@theinad.com>
 * @copyright 2018 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
final class ArteTest extends TestCase
{
    public function testDownloadInfoCanBeRetrievedFromValidDeUrlOriginalWithSubtitles(): void
    {
        $VALID_DOWNLOAD_URL = 'https://www.arte.tv/de/videos/073465-007-A/berlin-live-mike-the-mechanics/';
        $API_URL = 'https://api.arte.tv/api/player/v1/config/de/073465-007-A';
        $MEDIA_FILE_URL =
            'https://arteconcert-a.akamaihd.net/am/concert/' .
            '073000/073400/073465-007-A_SQ_0_VO-STF_03506192_MP4-2200_AMM-CONCERT-NEXT_uS8REPww5.mp4';

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
                $this->getFixture('arte/de/ows/videoPage.html'),
                $this->getFixture('arte/de/ows/apiResponse.json')
            );

        $arte = new Arte($logger, $tools);
        $result = $arte->getDownloadInfo($VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Berlin Live: Mike + The Mechanics', $result->getTitle());
        $this->assertEquals('', $result->getEpisodeTitle());
    }

    public function testDownloadInfoCanBeRetrievedFromValidDeUrlWithGermanOrigin(): void
    {
        $VALID_DOWNLOAD_URL = 'https://www.arte.tv/de/videos/080997-000-A/spaete-aufarbeitung/';
        $API_URL = 'https://api.arte.tv/api/player/v1/config/de/080997-000-A';
        $MEDIA_FILE_URL =
            'https://arteptweb-a.akamaihd.net/am/ptweb/080000/080900/' .
            '080997-000-A_SQ_0_VOA-STA_03502346_MP4-2200_AMM-PTWEB_uNbkEDM6T.mp4';

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
                $this->getFixture('arte/de/originDe/videoPage.html'),
                $this->getFixture('arte/de/originDe/apiResponse.json')
            );

        $arte = new Arte($logger, $tools);
        $result = $arte->getDownloadInfo($VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Späte Aufarbeitung', $result->getTitle());
        $this->assertEquals('', $result->getEpisodeTitle());
    }

    public function testDownloadInfoCanBeRetrievedFromValidDeUrlWithFrenchOrigin(): void
    {
        $VALID_DOWNLOAD_URL = 'https://www.arte.tv/de/videos/072310-040-A/karambolage/';
        $API_URL = 'https://api.arte.tv/api/player/v1/config/de/072310-040-A';
        $MEDIA_FILE_URL =
            'https://arteptweb-a.akamaihd.net/am/ptweb/072000/072300/' .
            '072310-040-A_SQ_0_VA_03389076_MP4-2200_AMM-PTWEB_sPpT1Jgi58.mp4';

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
                $this->getFixture('arte/de/originFr/videoPage.html'),
                $this->getFixture('arte/de/originFr/apiResponse.json')
            );

        $arte = new Arte($logger, $tools);
        $result = $arte->getDownloadInfo($VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Karambolage', $result->getTitle());
        $this->assertEquals('', $result->getEpisodeTitle());
    }

    public function testDownloadInfoCanBeRetrievedFromValidFrUrlOriginalWithSubtitles(): void
    {
        $VALID_DOWNLOAD_URL = 'https://www.arte.tv/fr/videos/073465-007-A/berlin-live-mike-the-mechanics/';
        $API_URL = 'https://api.arte.tv/api/player/v1/config/fr/073465-007-A';
        $MEDIA_FILE_URL =
            'https://arteconcert-a.akamaihd.net/am/concert/073000/073400/' .
            '073465-007-A_SQ_0_VO-STF_03506192_MP4-2200_AMM-CONCERT-NEXT_uS8REPww5.mp4';

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
                $this->getFixture('arte/fr/ows/videoPage.html'),
                $this->getFixture('arte/fr/ows/apiResponse.json')
            );

        $arte = new Arte($logger, $tools);
        $result = $arte->getDownloadInfo($VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Berlin Live : Mike + The Mechanics', $result->getTitle());
        $this->assertEquals('', $result->getEpisodeTitle());
    }

    public function testDownloadInfoCanBeRetrievedFromValidFrUrlWithGermanOrigin(): void
    {
        $VALID_DOWNLOAD_URL = 'https://www.arte.tv/fr/videos/080997-000-A/franco-vu-de-l-etranger/';
        $API_URL = 'https://api.arte.tv/api/player/v1/config/fr/080997-000-A';
        $MEDIA_FILE_URL =
            'https://arteptweb-a.akamaihd.net/am/ptweb/080000/080900/' .
            '080997-000-A_SQ_0_VF-STF_03502342_MP4-2200_AMM-PTWEB_uNZpEDLwk.mp4';

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
                $this->getFixture('arte/fr/originDe/videoPage.html'),
                $this->getFixture('arte/fr/originDe/apiResponse.json')
            );

        $arte = new Arte($logger, $tools);
        $result = $arte->getDownloadInfo($VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals("Franco vu de l'étranger", $result->getTitle());
        $this->assertEquals('', $result->getEpisodeTitle());
    }

    public function testDownloadInfoCanBeRetrievedFromValidFrUrlWithFrenchOrigin(): void
    {
        $VALID_DOWNLOAD_URL = 'https://www.arte.tv/fr/videos/072310-040-A/karambolage/';
        $API_URL = 'https://api.arte.tv/api/player/v1/config/fr/072310-040-A';
        $MEDIA_FILE_URL =
            'https://arteptweb-a.akamaihd.net/am/ptweb/072000/072300/' .
            '072310-040-A_SQ_0_VOF_03389080_MP4-2200_AMM-PTWEB_sPsY1JgixL.mp4';

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
                $this->getFixture('arte/fr/originFr/videoPage.html'),
                $this->getFixture('arte/fr/originFr/apiResponse.json')
            );

        $arte = new Arte($logger, $tools);
        $result = $arte->getDownloadInfo($VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Karambolage', $result->getTitle());
        $this->assertEquals('', $result->getEpisodeTitle());
    }
}
