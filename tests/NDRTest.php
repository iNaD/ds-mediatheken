<?php

namespace Tests;

use TheiNaD\DSMediatheken\Mediatheken\NDR;
use TheiNaD\DSMediatheken\Utils\Curl;
use TheiNaD\DSMediatheken\Utils\Logger;
use TheiNaD\DSMediatheken\Utils\Result;
use TheiNaD\DSMediatheken\Utils\Tools;

/**
 * Unit Test for NDR
 *
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2018-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
final class NDRTest extends TestCase
{
    public function testDownloadInfoCanBeRetrievedFromValidUrl(): void
    {
        $VALID_DOWNLOAD_URL = 'https://www.ndr.de/fernsehen/Sturm-der-Liebe,sendung827282.html';
        $MEDIA_FILE_URL = 'https://mediandr-a.akamaihd.net/progressive_geo/2018/1019/TV-20181019-0953-1900.hq.mp4';

        $logger = $this->createMock(Logger::class);
        $curl = $this->createMock(Curl::class);
        $tools = new Tools($logger, $curl);

        $curl->expects($this->exactly(1))
            ->method('request')
            ->withConsecutive(
                [$this->equalTo($VALID_DOWNLOAD_URL)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getFixture('ndr/videoPage.html')
            );

        $ndr = new NDR($logger, $tools);
        $result = $ndr->getDownloadInfo($VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Sturm der Liebe', $result->getTitle());
        $this->assertEquals('3019 vom 19.10.2018', $result->getEpisodeTitle());
    }

    public function testTitleWithoutEpisodeNumber(): void
    {
        $VALID_DOWNLOAD_URL =
            'https://www.ndr.de/fernsehen/sendungen/extra_3/extra-3-Spezial-Das-Beste,sendung827176.html';
        $MEDIA_FILE_URL = 'https://mediandr-a.akamaihd.net/progressive/2018/1019/TV-20181019-1321-3000.hq.mp4';

        $logger = $this->createMock(Logger::class);
        $curl = $this->createMock(Curl::class);
        $tools = new Tools($logger, $curl);

        $curl->expects($this->exactly(1))
            ->method('request')
            ->withConsecutive(
                [$this->equalTo($VALID_DOWNLOAD_URL)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getFixture('ndr/videoPageExtra3.html')
            );

        $ndr = new NDR($logger, $tools);
        $result = $ndr->getDownloadInfo($VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('extra 3 Spezial: Das Beste', $result->getTitle());
        $this->assertEquals('vom 17.10.2018', $result->getEpisodeTitle());
    }

    public function testTitleWithAlternateName(): void
    {
        $VALID_DOWNLOAD_URL = 'https://www.ndr.de/fernsehen/Vaterliebe,grossstadtrevier832.html';
        $MEDIA_FILE_URL = 'https://mediandr-a.akamaihd.net/progressive/2015/1007/TV-20151007-1613-1842.hq.mp4';

        $logger = $this->createMock(Logger::class);
        $curl = $this->createMock(Curl::class);
        $tools = new Tools($logger, $curl);

        $curl->expects($this->exactly(1))
            ->method('request')
            ->withConsecutive(
                [$this->equalTo($VALID_DOWNLOAD_URL)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getFixture('ndr/videoPageGrossstadtrevier.html')
            );

        $ndr = new NDR($logger, $tools);
        $result = $ndr->getDownloadInfo($VALID_DOWNLOAD_URL);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($MEDIA_FILE_URL, $result->getUri());
        $this->assertEquals('Großstadtrevier', $result->getTitle());
        $this->assertEquals('Vaterliebe', $result->getEpisodeTitle());
    }
}
