<?php
namespace TheiNaD\DSMediatheken;

use TheiNaD\DSMediatheken\Utils\Curl;
use TheiNaD\DSMediatheken\Utils\Tools;
use TheiNaD\DSMediatheken\Utils\Logger;
use TheiNaD\DSMediatheken\Mediatheken\ARD;
use TheiNaD\DSMediatheken\Mediatheken\RBB;
use TheiNaD\DSMediatheken\Mediatheken\WDR;
use TheiNaD\DSMediatheken\Mediatheken\ZDF;
use TheiNaD\DSMediatheken\Mediatheken\Arte;
use TheiNaD\DSMediatheken\Mediatheken\KiKA;
use TheiNaD\DSMediatheken\Mediatheken\DreiSat;
use TheiNaD\DSMediatheken\Mediatheken\NDR;

require_once dirname(__FILE__) . '/utils/defines.php';
require_once dirname(__FILE__) . '/utils/Curl.php';
require_once dirname(__FILE__) . '/utils/Logger.php';
require_once dirname(__FILE__) . '/utils/Tools.php';
include_once dirname(__FILE__) . '/mediatheken/ARD.php';
include_once dirname(__FILE__) . '/mediatheken/Arte.php';
include_once dirname(__FILE__) . '/mediatheken/DreiSat.php';
include_once dirname(__FILE__) . '/mediatheken/KiKA.php';
include_once dirname(__FILE__) . '/mediatheken/NDR.php';
include_once dirname(__FILE__) . '/mediatheken/RBB.php';
include_once dirname(__FILE__) . '/mediatheken/WDR.php';
include_once dirname(__FILE__) . '/mediatheken/ZDF.php';

/**
 * Provides download links for all Mediatheken.
 *
 * All public functions are required by Synology Download Station.
 *
 * @author Daniel Gehn <me@theinad.com>
 * @version 0.1.2
 * @copyright 2017-2018 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class SynoFileHostingMediathek
{

    const DEFAULT_LOG_PATH = '/tmp/mediathek.log';
    const MEDIATHEKEN = array(
        ARD::class,
        Arte::class,
        DreiSat::class,
        KiKA::class,
        NDR::class,
        RBB::class,
        WDR::class,
        ZDF::class
    );

    private $url;
    private $username;
    private $password;
    private $hostInfo;
    private $filename;

    private $logger = null;
    private $loggers = [];
    private $tools = null;
    private $logEnabled = false;
    private $logPath = null;
    private $logToFile = true;

    /**
     * Is called on construct by Download Station.
     *
     * @param string $url Download Url
     * @param string $username Login Username
     * @param string $password Login Password
     * @param string $hostInfo Hoster Info
     * @param string $filename Filename
     * @param boolean $debug Debug enabled or disabled
     * @param string $logPath Path to logfile
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

        $this->loggers[SynoFileHostingMediathek::class] = new Logger($this->logPath, SynoFileHostingMediathek::class, $this->logEnabled, $this->logToFile);
        $this->logger = $this->loggers[SynoFileHostingMediathek::class];
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
     * @return integer
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- the function name is given by Synology
    public function Verify($clearCookie = '')
    {
    }

    /**
     * Returns the Download URI to be used by Download Station.
     *
     * @return array|bool
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- the function name is given by Synology
    public function GetDownloadInfo()
    {
        if (strlen(trim($this->url)) === 0) {
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
     */
    private function findSupportingMediathek()
    {
        foreach (self::MEDIATHEKEN as $mediathek) {
            if ($mediathek::supportsUrl($this->url)) {
                $this->loggers[$mediathek] = new Logger($this->logPath, $mediathek, $this->logEnabled, $this->logToFile);
                return new $mediathek($this->loggers[$mediathek], $this->tools);
            }
        }

        return null;
    }

    private function toDownloadInfo($result)
    {
        if ($result === null || !$result->hasUri()) {
            return false;
        }

        $downloadInfo = array();
        $downloadInfo[DOWNLOAD_URL] = $result->getUri();
        $downloadInfo[DOWNLOAD_FILENAME] = $this->filenameForResult($result);

        return $downloadInfo;
    }

    private function filenameForResult($result)
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
            $events = array_merge($events, $logger->getEvents());
        }

        usort($events, function ($a, $b) {
            return $a['timestamp'] - $b['timestamp'];
        });

        return $events;
    }
}
