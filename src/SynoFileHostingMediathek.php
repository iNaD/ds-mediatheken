<?php

namespace TheiNaD\DSMediatheken;

use RuntimeException;
use TheiNaD\DSMediatheken\Mediatheken\ARD;
use TheiNaD\DSMediatheken\Mediatheken\Arte;
use TheiNaD\DSMediatheken\Mediatheken\BR;
use TheiNaD\DSMediatheken\Mediatheken\DreiSat;
use TheiNaD\DSMediatheken\Mediatheken\KiKa;
use TheiNaD\DSMediatheken\Mediatheken\MDR;
use TheiNaD\DSMediatheken\Mediatheken\NDR;
use TheiNaD\DSMediatheken\Mediatheken\RBB;
use TheiNaD\DSMediatheken\Mediatheken\WDR;
use TheiNaD\DSMediatheken\Mediatheken\ZDF;
use TheiNaD\DSMediatheken\Utils\Curl;
use TheiNaD\DSMediatheken\Utils\Logger;
use TheiNaD\DSMediatheken\Utils\Mediathek;
use TheiNaD\DSMediatheken\Utils\Result;
use TheiNaD\DSMediatheken\Utils\Tools;

// phpcs:disable
require_once __DIR__ . '/Utils/defines.php';
require_once __DIR__ . '/Utils/Mediathek.php';
require_once __DIR__ . '/Utils/Result.php';
require_once __DIR__ . '/Utils/Curl.php';
require_once __DIR__ . '/Utils/Logger.php';
require_once __DIR__ . '/Utils/Tools.php';
include_once __DIR__ . '/Mediatheken/ARD.php';
include_once __DIR__ . '/Mediatheken/Arte.php';
include_once __DIR__ . '/Mediatheken/BR.php';
include_once __DIR__ . '/Mediatheken/KiKa.php';
include_once __DIR__ . '/Mediatheken/MDR.php';
include_once __DIR__ . '/Mediatheken/NDR.php';
include_once __DIR__ . '/Mediatheken/RBB.php';
include_once __DIR__ . '/Mediatheken/WDR.php';
include_once __DIR__ . '/Mediatheken/ZDF.php';
include_once __DIR__ . '/Mediatheken/DreiSat.php';
// phpcs:enable

/**
 * Provides download links for all Mediatheken.
 *
 * All public functions are required by Synology Download Station.
 *
 * @author    Daniel Gehn <me@theinad.com>
 * @version   0.6.2
 * @copyright 2017-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
class SynoFileHostingMediathek
{
    const DEFAULT_LOG_PATH = '/tmp/mediathek.log';
    const MEDIATHEKEN = [
        ARD::class,
        Arte::class,
        BR::class,
        DreiSat::class,
        KiKa::class,
        MDR::class,
        NDR::class,
        RBB::class,
        WDR::class,
        ZDF::class,
    ];

    private $url;
    private $username;
    private $password;
    private $hostInfo;
    private $filename;

    private $logger;
    private $loggers = [];
    private $tools;
    private $logEnabled;
    private $logPath;
    private $logToFile;

    /**
     * Is called on construct by Download Station.
     *
     * @param string  $url       Download Url
     * @param string  $username  Login Username
     * @param string  $password  Login Password
     * @param string  $hostInfo  Hoster Info
     * @param string  $filename  Filename
     * @param boolean $debug     Debug enabled or disabled
     * @param string  $logPath   Path to logfile
     * @param boolean $logToFile Whether to log into file or not
     */
    public function __construct(
        $url,
        $username = '',
        $password = '',
        $hostInfo = '',
        $filename = '',
        $debug = false,
        $logPath = null,
        $logToFile = true
    ) {
        $this->logPath = $logPath !== null ? $logPath : self::DEFAULT_LOG_PATH;
        $this->logToFile = $logToFile;
        $this->logEnabled = $debug;

        $this->loggers[__CLASS__] =
            new Logger(
                $this->logPath,
                __CLASS__,
                $this->logEnabled,
                $this->logToFile
            );
        $this->logger = $this->loggers[__CLASS__];
        $this->loggers[Tools::class] = new Logger($this->logPath, Tools::class, $this->logEnabled, $this->logToFile);
        $this->tools = new Tools($this->loggers[Tools::class], new Curl());

        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
        $this->hostInfo = $hostInfo;
        $this->filename = $filename;

        $this->logger->log("URL: $url");
    }

    /**
     * Is called after the download finishes
     *
     * @return void
     */
    public function onDownloaded()
    {
    }

    /**
     * Verifies the Account
     *
     * @param string $clearCookie
     *
     * @return integer|void
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- the function name is given by Synology
    public function Verify($clearCookie = '')
    {
    }

    /**
     * Returns the Download URI to be used by Download Station.
     *
     * @return array|bool
     *
     * @throws RuntimeException
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- the function name is given by Synology
    public function GetDownloadInfo()
    {
        if (trim($this->url) === '') {
            $this->logger->log('URL is empty');

            return false;
        }

        $mediathek = $this->findSupportingMediathek();
        if ($mediathek === null) {
            $this->logger->log('Failed to find mediathek for ' . $this->url);

            return false;
        }

        return $this->toDownloadInfo($mediathek->getDownloadInfo(
            $this->url,
            $this->username,
            $this->password
        ));
    }

    /**
     * @return Mediathek
     *
     * @throws RuntimeException
     */
    private function findSupportingMediathek()
    {
        foreach (self::MEDIATHEKEN as $mediathek) {
            /** @var Mediathek $mediathek */
            if ($mediathek::supportsUrl($this->url)) {
                $this->loggers[$mediathek] =
                    new Logger(
                        $this->logPath,
                        $mediathek,
                        $this->logEnabled,
                        $this->logToFile
                    );

                return new $mediathek($this->loggers[$mediathek], $this->tools);
            }
        }

        return null;
    }

    /**
     * @param Result|null $result
     *
     * @return array|bool
     */
    private function toDownloadInfo($result)
    {
        if ($result === null || !$result->hasUri()) {
            return false;
        }

        $downloadInfo = [];
        $downloadInfo[DOWNLOAD_URL] = $result->getUri();
        $downloadInfo[DOWNLOAD_FILENAME] = $this->filenameForResult($result);

        return $downloadInfo;
    }

    /**
     * @param Result $result
     *
     * @return string
     */
    private function filenameForResult(Result $result)
    {
        $videoTitle = $this->tools->videoTitle($result->getTitle(), $result->getEpisodeTitle());

        return $this->tools->buildFilename($result->getUri(), $videoTitle);
    }

    /**
     * Retrieve all logged events by log.
     *
     * @return array
     */
    public function getLogs()
    {
        $logs = [];

        foreach ($this->loggers as $name => $logger) {
            $logs[$name] = $logger->getEvents();
        }

        return $logs;
    }

    /**
     * Retrieve all logged events combined.
     *
     * @return array
     */
    public function getCombinedLog()
    {
        $events = [];

        foreach ($this->loggers as $name => $logger) {
            $events[] = $logger->getEvents();
        }

        $events = array_merge([], ...$events);

        usort($events, function ($a, $b) {
            return $a['timestamp'] - $b['timestamp'];
        });

        return $events;
    }
}
