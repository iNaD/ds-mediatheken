<?php

namespace Tests;

use TheiNaD\DSMediatheken\Mediatheken\KiKa;
use TheiNaD\DSMediatheken\Utils\Curl;
use TheiNaD\DSMediatheken\Utils\Logger;
use TheiNaD\DSMediatheken\Utils\Result;
use TheiNaD\DSMediatheken\Utils\Tools;

/**
 * Unit Test for KiKa
 *
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2018-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
final class KiKaTest extends TestCase
{
    public function testDownloadInfoCanBeRetrievedFromValidUrl(): void
    {
        $VALID_DOWNLOAD_URL = 'https://www.kika.de/sherlock-yack-der-zoodetektiv/sendungen/sendung61934.html';
        $API_URL = 'https://www.kika.de/sherlock-yack-der-zoodetektiv/sendungen/videos/video11510-avCustom.xml';
        $MEDIA_FILE_URL =
            'https://pmdgeokika-a.akamaihd.net/mp4dyn/c/FCMS-cab986f0-3635-4f80-a83a-ed4c4e2de77f-5a2c8da1cdb7_ca.mp4';

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
                $this->getFixture('kika/videoPage.html'),
                $this->getFixture('kika/apiResponse.xml')
            );

        $kika = new KiKa($logger, $tools);
        $result = $kika->getDownloadInfo($VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('SHERLOCK YACK - Der Zoodetektiv', $result->getTitle());
        $this->assertEquals('24. Wer hat das Zebra bepinselt?', $result->getEpisodeTitle());
    }
}
