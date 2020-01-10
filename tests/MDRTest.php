<?php

namespace Tests;

use TheiNaD\DSMediatheken\Mediatheken\MDR;
use TheiNaD\DSMediatheken\Utils\Curl;
use TheiNaD\DSMediatheken\Utils\Logger;
use TheiNaD\DSMediatheken\Utils\Result;
use TheiNaD\DSMediatheken\Utils\Tools;

/**
 * Unit Test for MDR
 *
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2018-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
final class MDRTest extends TestCase
{
    public function testDownloadInfoCanBeRetrievedFromValidUrl(): void
    {
        $VALID_DOWNLOAD_URL = 'https://www.mdr.de/mediathek/video-243082_zc-89922dc9_zs-df360c07.html';
        $API_URL = 'https://www.mdr.de/mediathek/video-243082-avCustom.xml';
        $MEDIA_FILE_URL =
            'https://odmdr-a.akamaihd.net/mp4dyn2/9/FCMS-960f06bd-41fe-4c51-a55b-35518639e6e3-be7c2950aac6_96.mp4';

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
                $this->getFixture('mdr/videoPage.html'),
                $this->getFixture('mdr/apiResponse.xml')
            );

        $mdr = new MDR($logger, $tools);
        $result = $mdr->getDownloadInfo($VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Filme', $result->getTitle());
        $this->assertEquals('Verliebt in Amsterdam', $result->getEpisodeTitle());
    }
}
