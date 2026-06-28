<?php

namespace Tests;

use TheiNaD\DSMediatheken\Mediatheken\ZDF;
use TheiNaD\DSMediatheken\Utils\Curl;
use TheiNaD\DSMediatheken\Utils\Logger;
use TheiNaD\DSMediatheken\Utils\Result;
use TheiNaD\DSMediatheken\Utils\Tools;

/**
 * Unit Test for ZDF
 *
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2018-2022 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
final class ZDFTest extends TestCase
{
    public function testDownloadFromValidUrl(): void
    {
        $DOWNLOAD_URL = 'https://www.zdf.de/filme/toubab-movie-100';
        // The page embeds the ptmd template directly; the provider only substitutes the player id
        // and prefixes the api host - no intermediate content document is fetched anymore.
        $PTMD_URL = 'https://api.zdf.de/tmd/2/ngplayer_2_3/vod/ptmd/mediathek/230828_2015_sendung_dks/7';
        $MEDIA_FILE_URL = 'https://nrodlzdf-a.akamaihd.net/dach/zdf/23/08/230828_2015_sendung_dks/1/' .
            '230828_2015_sendung_dks_a1a2_1628k_p13v15.mp4';

        $logger = $this->createMock(Logger::class);
        $curl = $this->createMock(Curl::class);
        $tools = new Tools($logger, $curl);

        $curl->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                [$this->equalTo($DOWNLOAD_URL)],
                [$this->equalTo($PTMD_URL)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getFixture('zdf/videoPage.html'),
                $this->getFixture('zdf/ptmd.json')
            );

        $zdf = new ZDF($logger, $tools);
        $result = $zdf->getDownloadInfo($DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Toubab', $result->getTitle());
        $this->assertEquals('', $result->getEpisodeTitle());
    }
}
