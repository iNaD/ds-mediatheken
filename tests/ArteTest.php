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
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2018-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
final class ArteTest extends TestCase
{
    public function testDownloadInfoCanBeRetrievedFromValidDeUrlOriginalWithSubtitles(): void
    {
        $VALID_DOWNLOAD_URL = 'https://www.arte.tv/de/videos/088376-000-A/lou-andreas-salome/';
        $API_URL = 'https://api.arte.tv/api/player/v1/config/de/088376-000-A';
        $MEDIA_FILE_URL =
            'https://arteptweb-a.akamaihd.net/am/ptweb/088000/088300/' .
            '088376-000-A_SQ_0_VOA-STA_04785373_MP4-2200_AMM-PTWEB_1HrcvDkhx2.mp4';

        $logger = $this->createMock(Logger::class);
        $curl = $this->createMock(Curl::class);
        $tools = new Tools($logger, $curl);

        $curl->expects($this->exactly(3))
            ->method('request')
            ->withConsecutive(
                [$this->equalTo($VALID_DOWNLOAD_URL)],
                [$this->equalTo(Arte::STATIC_APIPLAYER_JSON_URL)],
                [$this->equalTo($API_URL)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getFixture('arte/videoPage.html'),
                $this->getFixture('arte/general.json'),
                $this->getFixture('arte/apiResponse.json')
            );

        $arte = new Arte($logger, $tools);
        $result = $arte->getDownloadInfo($VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Lou Andreas-Salomé', $result->getTitle());
        $this->assertEquals('', $result->getEpisodeTitle());
    }
}
