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
        $VALID_DOWNLOAD_URL =
            'https://www.ardmediathek.de/video/filmmittwoch-im-ersten/geliefert-oder-drama/das-erste/' .
            'Y3JpZDovL2Rhc2Vyc3RlLmRlL2ZpbG1taXR0d29jaCBpbSBlcnN0ZW4vMjE4MzkzZTMtYjIyMy00Njg2LWJmNTMtM2M2NTg5MzIxYzMw/';
        $API_URL = ARD::$API_BASE_URL .
            'Y3JpZDovL2Rhc2Vyc3RlLmRlL2ZpbG1taXR0d29jaCBpbSBlcnN0ZW4vMjE4MzkzZTMtYjIyMy00Njg2LWJmNTMtM2M2NTg5MzIxYzMw';
        $MEDIA_FILE_URL =
            'https://pdvideosdaserste-a.akamaihd.net/int/2021/10/04/218393e3-b223-4686-bf53-3c6589321c30/' .
            '1920-1_978535.mp4';

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
        $this->assertEquals('FilmMittwoch im Ersten', $result->getTitle());
        $this->assertEquals('Geliefert', $result->getEpisodeTitle());
    }
}
