<?php

namespace Tests;

use TheiNaD\DSMediatheken\Mediatheken\WDR;
use TheiNaD\DSMediatheken\Utils\Curl;
use TheiNaD\DSMediatheken\Utils\Logger;
use TheiNaD\DSMediatheken\Utils\Result;
use TheiNaD\DSMediatheken\Utils\Tools;

/**
 * Unit Test for WDR
 *
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2018-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
final class WDRTest extends TestCase
{
    protected static $VALID_DOWNLOAD_URL =
        'https://www1.wdr.de/mediathek/video/sendungen/fernsehfilm/video-pfarrer-braun-ausgegeigt-100.html';
    protected static $API_URL = 'http://deviceids-medp.wdr.de/ondemand/151/1516562.js';
    protected static $MEDIA_FILE_URL =
        'https://wdrmedien-a.akamaihd.net/medp/ondemand/weltweit/fsk0/151/1516562/1516562_17387593.mp4';

    public function testDownloadInfoCanBeRetrievedFromValidUrl(): void
    {
        $logger = $this->createMock(Logger::class);
        $curl = $this->createMock(Curl::class);
        $tools = new Tools($logger, $curl);

        $curl->expects($this->exactly(2))
            ->method('requestMobile')
            ->withConsecutive(
                [$this->equalTo(self::$VALID_DOWNLOAD_URL)],
                [$this->equalTo(self::$API_URL)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getFixture('wdr/videoPage.html'),
                $this->getFixture('wdr/apiResponse.js')
            );

        $wdr = new WDR($logger, $tools);
        $result = $wdr->getDownloadInfo(self::$VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(self::$MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Fernsehfilm', $result->getTitle());
        $this->assertEquals('Pfarrer Braun: Ausgegeigt!', $result->getEpisodeTitle());
    }
}
