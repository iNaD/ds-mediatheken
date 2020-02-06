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
 * @copyright 2018-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
final class ZDFTest extends TestCase
{
    protected static $VALID_DOWNLOAD_URL = 'https://www.zdf.de/kinder/bibi-blocksberg/das-grosse-besenrennen-102.html';
    protected static $EPISODE_DETAILS_URL =
        'https://api.zdf.de/content/documents/zdf/kinder/bibi-blocksberg' .
        '/das-grosse-besenrennen-102.json?profile=player';
    protected static $FORMITAETEN_URL =
        'https://api.zdf.de/tmd/2/ngplayer_2_3/vod/ptmd/tivi/180217_besenrennen_folge51_bib';
    protected static $MEDIA_FILE_URL =
        'https://nrodlzdf-a.akamaihd.net/de/tivi/18/02/' .
        '180217_besenrennen_folge51_bib/3/180217_besenrennen_folge51_bib_1496k_p13v13.mp4';
    protected static $AD_VALID_DOWNLOAD_URL = 'https://www.zdf.de/serien/bad-banks/schoene-neue-welt-138.html';
    protected static $AD_EPISODE_DETAILS_URL =
        'https://api.zdf.de/content/documents/zdf/serien/bad-banks/schoene-neue-welt-138.json?profile=player2';
    protected static $AD_FORMITAETEN_URL =
        'https://api.zdf.de/tmd/2/ngplayer_2_3/vod/ptmd/mediathek/200208_2145_sendung_bad';
    protected static $AD_MEDIA_FILE_URL =
        'https://nrodlzdf-a.akamaihd.net/dach/zdf/20/02/200208_2145_sendung_bad/3/' .
        '200208_2145_sendung_bad_a1a2_1496k_p13v14.mp4';

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
                $this->getFixture('zdf/videoPage.html'),
                $this->getFixture('zdf/episodeDetails.json'),
                $this->getFixture('zdf/formitaeten.json')
            );

        $zdf = new ZDF($logger, $tools);
        $result = $zdf->getDownloadInfo(self::$VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(self::$MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Bibi Blocksberg', $result->getTitle());
        $this->assertEquals('Das große Besenrennen', $result->getEpisodeTitle());
    }

    public function testDownloadWithAudioDescriptionAvailable(): void
    {
        $logger = $this->createMock(Logger::class);
        $curl = $this->createMock(Curl::class);
        $tools = new Tools($logger, $curl);

        $curl->expects($this->exactly(3))
            ->method('request')
            ->withConsecutive(
                [$this->equalTo(self::$AD_VALID_DOWNLOAD_URL)],
                [$this->equalTo(self::$AD_EPISODE_DETAILS_URL)],
                [$this->equalTo(self::$AD_FORMITAETEN_URL)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getFixture('zdf/ad/videoPage.html'),
                $this->getFixture('zdf/ad/episodeDetails.json'),
                $this->getFixture('zdf/ad/formitaeten.json')
            );

        $zdf = new ZDF($logger, $tools);
        $result = $zdf->getDownloadInfo(self::$AD_VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(self::$AD_MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Bad Banks', $result->getTitle());
        $this->assertEquals('Schöne neue Welt', $result->getEpisodeTitle());
    }
}
