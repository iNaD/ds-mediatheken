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
 * @copyright 2018-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
final class BRTest extends TestCase
{
    protected static $VALID_DOWNLOAD_URL = 'https://www.br.de/mediathek/video/heiter-bis-toedlich-alles-klara-mord-' .
    'nach-feierabend-av:5dd560152786bb001a4ef549';
    protected static $RELAY_BATCH_URL = 'https://api.mediathek.br.de/graphql/relayBatch';
    protected static $MEDIA_URL = 'https://cdn-storage.br.de/geo/b7/2020-01/10/a58e8de833a411eaa0b0984be10adece_X.mp4';

    public function testDownloadInfoCanBeRetrievedFromValidUrl(): void
    {
        $logger = $this->createMock(Logger::class);
        $curl = $this->createMock(Curl::class);
        $tools = new Tools($logger, $curl);

        $curl->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                [$this->equalTo(self::$VALID_DOWNLOAD_URL)],
                [$this->equalTo(self::$RELAY_BATCH_URL)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getFixture('br/videoPage.html'),
                $this->getFixture('br/relayBatchResponse.json')
            );

        $br = new BR($logger, $tools);
        $result = $br->getDownloadInfo(self::$VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertNotEmpty($result->getUri());
        $this->assertNotEmpty($result->getTitle());
        $this->assertNotEmpty($result->getEpisodeTitle());
        $this->assertEquals(self::$MEDIA_URL, $result->getUri());
        $this->assertEquals('Heiter bis tödlich - Alles Klara', $result->getTitle());
        $this->assertEquals('Mord nach Feierabend', $result->getEpisodeTitle());
    }
}
