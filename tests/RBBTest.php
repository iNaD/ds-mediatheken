<?php

namespace Tests;

use TheiNaD\DSMediatheken\Mediatheken\RBB;
use TheiNaD\DSMediatheken\Utils\Curl;
use TheiNaD\DSMediatheken\Utils\Logger;
use TheiNaD\DSMediatheken\Utils\Result;
use TheiNaD\DSMediatheken\Utils\Tools;

/**
 * Unit Test for RBB
 *
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2018-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
final class RBBTest extends TestCase
{
    protected static $VALID_DOWNLOAD_URL =
        'http://www.ardmediathek.de/tv/Filme-im-Ersten/' .
        'St-Josef-am-Berg-Berge-auf-Probe/Das-Erste/Video?bcastId=1933898&documentId=50077518';
    protected static $API_URL = 'http://mediathek.rbb-online.de/play/media/50077518';
    protected static $MEDIA_FILE_URL =
        'https://rbbmediapmdp-a.akamaihd.net/content/' .
        '2a/b0/2ab01992-ddd6-4b8e-a6cc-acfe4b2b4a33/8939789a-f089-41ba-bd33-10fd79f662b4_1800k.mp4';

    public function testDownloadInfoCanBeRetrievedFromValidUrl(): void
    {
        $logger = $this->createMock(Logger::class);
        $curl = $this->createMock(Curl::class);
        $tools = new Tools($logger, $curl);

        $curl->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                [$this->equalTo(self::$API_URL)],
                [$this->equalTo(self::$VALID_DOWNLOAD_URL)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getFixture('rbb/apiResponse.json'),
                $this->getFixture('rbb/videoPage.html')
            );

        $rbb = new RBB($logger, $tools);
        $result = $rbb->getDownloadInfo(self::$VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(self::$MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Mord mit Aussicht (5): Waldeslust', $result->getTitle());
        $this->assertEquals('', $result->getEpisodeTitle());
    }
}
