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
    /**
     * @dataProvider dataProvider
     *
     * @param string $downloadUrl
     * @param string $downloadFixture
     * @param string $episodeDetailsUrl
     * @param string $episodeDetailsFixture
     * @param string $formitaetenUrl
     * @param string $formitaetenFixture
     * @param string $mediaFileUrl
     * @param string $title
     * @param string $episodeTitle
     *
     * @return void
     */
    public function testDownloadFromValidUrl($downloadUrl, $downloadFixture, $episodeDetailsUrl, $episodeDetailsFixture, $formitaetenUrl, $formitaetenFixture, $mediaFileUrl, $title, $episodeTitle): void
    {
        $logger = $this->createMock(Logger::class);
        $curl = $this->createMock(Curl::class);
        $tools = new Tools($logger, $curl);

        $curl->expects($this->exactly(3))
            ->method('request')
            ->withConsecutive(
                [$this->equalTo($downloadUrl)],
                [$this->equalTo($episodeDetailsUrl)],
                [$this->equalTo($formitaetenUrl)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getFixture($downloadFixture),
                $this->getFixture($episodeDetailsFixture),
                $this->getFixture($formitaetenFixture)
            );

        $zdf = new ZDF($logger, $tools);
        $result = $zdf->getDownloadInfo($downloadUrl);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($mediaFileUrl, $result->getUri());
        $this->assertEquals($title, $result->getTitle());
        $this->assertEquals($episodeTitle, $result->getEpisodeTitle());
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            [
                'https://www.zdf.de/kinder/bibi-blocksberg/das-grosse-besenrennen-102.html',
                'zdf/videoPage.html',
                'https://api.zdf.de/content/documents/zdf/kinder/bibi-blocksberg/das-grosse-besenrennen-102.json?' .
                    'profile=player',
                'zdf/episodeDetails.json',
                'https://api.zdf.de/tmd/2/ngplayer_2_3/vod/ptmd/tivi/180217_besenrennen_folge51_bib',
                'zdf/formitaeten.json',
                'https://nrodlzdf-a.akamaihd.net/de/tivi/18/02/180217_besenrennen_folge51_bib/3/' .
                    '180217_besenrennen_folge51_bib_1496k_p13v13.mp4',
                'Bibi Blocksberg',
                'Das große Besenrennen',
            ],
            [
                'https://www.zdf.de/serien/bad-banks/schoene-neue-welt-138.html',
                'zdf/ad/videoPage.html',
                'https://api.zdf.de/content/documents/zdf/serien/bad-banks/schoene-neue-welt-138.json?profile=player2',
                'zdf/ad/episodeDetails.json',
                'https://api.zdf.de/tmd/2/ngplayer_2_3/vod/ptmd/mediathek/200208_2145_sendung_bad',
                'zdf/ad/formitaeten.json',
                'https://nrodlzdf-a.akamaihd.net/dach/zdf/20/02/200208_2145_sendung_bad/3/' .
                    '200208_2145_sendung_bad_a1a2_1496k_p13v14.mp4',
                'Bad Banks',
                'Schöne neue Welt',
            ],
            [
                'https://www.zdf.de/arte/krieg-der-traume-1918-1939/' .
                    'page-video-artede-krieg-der-traeume---1918-1939---ueberleben-100.html',
                'zdf/streamsDefault/videoPage.html',
                'https://api.zdf.de/content/documents/zdf/arte/krieg-der-traume-1918-1939/' .
                    'page-video-artede-krieg-der-traeume---1918-1939---ueberleben-100.json?profile=player2',
                'zdf/streamsDefault/episodeDetails.json',
                'https://api.zdf.de/content/documents/vod-artede-krieg-der-traeume---1918-1939---ueberleben-100.json?' .
                    'profile=tmd',
                'zdf/streamsDefault/formitaeten.json',
                'https://arte-zdf-mediathek.akamaized.net/am/mp4/067000/067200/' .
                    '067244-001-B_SQ_1_VO-STA_06376548_MP4-2200_AMM-IPTV-ZDF_1gyNi1LBGJW.mp4',
                'Krieg der Träume 1918-1939',
                'Krieg der Träume - 1918-1939 - Überleben',
            ]
        ];
    }
}
