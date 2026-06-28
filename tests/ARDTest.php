<?php

namespace Tests;

use TheiNaD\DSMediatheken\Mediatheken\ARD;
use TheiNaD\DSMediatheken\Utils\Curl;
use TheiNaD\DSMediatheken\Utils\Logger;
use TheiNaD\DSMediatheken\Utils\Result;
use TheiNaD\DSMediatheken\Utils\Tools;

/**
 * Unit Test for ARD
 *
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2018-2022 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
final class ARDTest extends TestCase
{
    public function testDownloadInfoCanBeRetrievedFromValidUrl(): void
    {
        // Film pages link to a "grouping" document id (suffix "-movie"). The provider has to
        // resolve it to the playable item id before querying the API.
        $VALID_DOWNLOAD_URL =
            'https://www.ardmediathek.de/film/toubab/' .
            'Y3JpZDovL3pkZi5kZS9QUk9EMS9TQ01TX2ZiODZlYWFjLWQwNzUtNDcxMS1hYzNiLWMxNDgxMDA0MmYyZS1tb3ZpZQ';
        $API_URL = ARD::$API_BASE_URL .
            'Y3JpZDovL3pkZi5kZS9QUk9EMS9TQ01TX2ZiODZlYWFjLWQwNzUtNDcxMS1hYzNiLWMxNDgxMDA0MmYyZQ';
        $MEDIA_FILE_URL =
            'https://nrodlzdf-a.akamaihd.net/dach/zdf/23/08/230828_2015_sendung_dks/1/' .
            '230828_2015_sendung_dks_a3a4_3328k_p15v15.mp4';

        $logger = $this->createMock(Logger::class);
        $curl = $this->createMock(Curl::class);
        $tools = new Tools($logger, $curl);

        $curl->expects($this->exactly(1))
            ->method('request')
            ->withConsecutive(
                [$this->equalTo($API_URL)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getFixture('ard/apiResponse.json')
            );

        $ard = new ARD($logger, $tools);
        $result = $ard->getDownloadInfo($VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Toubab', $result->getTitle());
        $this->assertEquals('Toubab', $result->getEpisodeTitle());
    }
}
