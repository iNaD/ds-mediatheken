<?php

namespace Tests;

use TheiNaD\DSMediatheken\Mediatheken\BR;
use TheiNaD\DSMediatheken\Utils\Curl;
use TheiNaD\DSMediatheken\Utils\Logger;
use TheiNaD\DSMediatheken\Utils\Result;
use TheiNaD\DSMediatheken\Utils\Tools;

/**
 * Unit Test for BR
 *
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2018-2022 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
final class BRTest extends TestCase
{
    protected static $VALID_DOWNLOAD_URL = 'https://www.br.de/mediathek/video/krimi-mit-ottfried-fischer-der-bulle' .
        '-von-toelz-der-mord-im-kloster-av:61a608e395643a000747383e';
    protected static $API_URL = 'https://api.mediathek.br.de/graphql';
    protected static $MEDIA_URL = 'https://cdn-storage.br.de/geo/MUJIuUOVBwQIbtC2uKJDM6OhuLnC_2rH_71S/_-iS/' .
        '_2rg5-rc5U1S/159b2b96-5aa5-4453-9800-a4d554c54092_X.mp4';

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
                $this->getFixture('br/api.json')
            );

        $br = new BR($logger, $tools);
        $result = $br->getDownloadInfo(self::$VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertNotEmpty($result->getUri());
        $this->assertNotEmpty($result->getTitle());
        $this->assertNotEmpty($result->getEpisodeTitle());
        $this->assertEquals(self::$MEDIA_URL, $result->getUri());
        $this->assertEquals('Krimi mit Ottfried Fischer', $result->getTitle());
        $this->assertEquals('Der Bulle von Tölz: Der Mord im Kloster', $result->getEpisodeTitle());
    }
}
