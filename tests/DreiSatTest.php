<?php

namespace Tests;

use TheiNaD\DSMediatheken\Mediatheken\DreiSat;
use TheiNaD\DSMediatheken\Utils\Curl;
use TheiNaD\DSMediatheken\Utils\Logger;
use TheiNaD\DSMediatheken\Utils\Result;
use TheiNaD\DSMediatheken\Utils\Tools;

/**
 * Unit Test for DreiSat
 *
 * @author Daniel Gehn <me@theinad.com>
 * @copyright 2018 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
final class DreiSatTest extends TestCase
{
    protected static $VALID_DOWNLOAD_URL = 'http://www.3sat.de/mediathek/?mode=play&obj=48258';
    protected static $API_URL = 'http://www.3sat.de/mediathek/xmlservice/web/beitragsDetails?ak=web&id=48258&ak=web';
    protected static $MEDIA_FILE_URL =
        'http://tvdl.zdf.de/dach/3sat/14/12/141213_meisterfaelscher3_online' .
        '/5/141213_meisterfaelscher3_online_2328k_p35v11.mp4';

    public function testDownloadInfoCanBeRetrievedFromValidUrl(): void
    {
        $logger = $this->createMock(Logger::class);
        $curl = $this->createMock(Curl::class);
        $tools = new Tools($logger, $curl);

        $curl->expects($this->exactly(1))
            ->method('request')
            ->withConsecutive(
                [$this->equalTo(self::$API_URL)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getFixture('dreiSat/apiResponse.xml')
            );

        $dreiSat = new DreiSat($logger, $tools);
        $result = $dreiSat->getDownloadInfo(self::$VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(self::$MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('3sat', $result->getTitle());
        $this->assertEquals('<![CDATA[Der Meisterfälscher]]>', $result->getEpisodeTitle());
    }
}
