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
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2018-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
final class DreiSatTest extends TestCase
{
    protected static $VALID_DOWNLOAD_URL = 'https://www.3sat.de/film/spielfilm/' .
    'reykjavik-rotterdam-toedliche-lieferung-100.html';

    protected static $EPISODE_DETAILS_URL =
        'https://api.3sat.de/content/documents/zdf/film/spielfilm/reykjavik-rotterdam-toedliche-lieferung-100.json' .
        '?profile=player2';

    protected static $FORMITAETEN_URL =
        'https://api.3sat.de/tmd/2/ngplayer_2_3/vod/ptmd/3sat/190809_reykjavik_rotterdam_krimisommer';

    protected static $MEDIA_FILE_URL =
        'https://nrodlzdf-a.akamaihd.net/dach/3sat/19/08/190809_reykjavik_rotterdam_krimisommer/2/' .
        '190809_reykjavik_rotterdam_krimisommer_1496k_p13v13.mp4';

    public function testDownloadInfoCanBeRetrievedFromValidUrl(): void
    {
        $logger = $this->createMock(Logger::class);
        $curl = $this->createMock(Curl::class);
        $tools = new Tools($logger, $curl);

        $curl->expects($this->exactly(3))
            ->method('request')
            ->withConsecutive(
                [$this->equalTo(self::$VALID_DOWNLOAD_URL)],
                [$this->equalTo(self::$EPISODE_DETAILS_URL)],
                [$this->equalTo(self::$FORMITAETEN_URL)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getFixture('dreiSat/videoPage.html'),
                $this->getFixture('dreiSat/episodeDetails.json'),
                $this->getFixture('dreiSat/formitaeten.json')
            );

        $dreiSat = new DreiSat($logger, $tools);
        $result = $dreiSat->getDownloadInfo(self::$VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(self::$MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Spielfilm', $result->getTitle());
        $this->assertEquals('Reykjavik - Rotterdam: Tödliche Lieferung', $result->getEpisodeTitle());
    }
}
