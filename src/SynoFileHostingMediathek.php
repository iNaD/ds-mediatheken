<?php
require_once dirname(__FILE__) . '/utils/defines.php';
require_once dirname(__FILE__) . '/utils/Logger.php';
require_once dirname(__FILE__) . '/utils/Tools.php';
include_once dirname(__FILE__) . '/mediatheken/ARD.php';
include_once dirname(__FILE__) . '/mediatheken/DreiSat.php';
include_once dirname(__FILE__) . '/mediatheken/RBB.php';
include_once dirname(__FILE__) . '/mediatheken/WDR.php';
include_once dirname(__FILE__) . '/mediatheken/ZDF.php';

/**
 * Provides download links for all Mediatheken.
 *
 * All public functions are required by Synology Download Station.
 *
 * @author Daniel Gehn <me@theinad.com>
 * @version 0.0.2
 * @copyright 2017 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class SynoFileHostingMediathek
{

    private static $LOG_PATH = '/tmp/mediathek.log';
    private static $LOG_PREFIX = 'SynoFileHostingMediathek';
    private static $LOG_PREFIX_TOOLS = 'Tools';
    private static $MEDIATHEKEN = array(
    ARD::class,
    DreiSat::class,
    RBB::class,
    WDR::class,
    ZDF::class
    );

    private $url;
    private $username;
    private $password;
    private $hostInfo;
    private $filename;

    private $logger;
    private $tools;
    private $logEnabled = false;
    private $logPath = false;

  /**
   * Is called on construct by Download Station.
   *
   * @param string $url Download Url
   * @param string $username Login Username
   * @param string $password Login Password
   * @param string $hostInfo Hoster Info
   * @param string $filename Filename
   * @param boolean $debug Debug enabled or disabled
   */
    public function __construct(
        $url,
        $username = '',
        $password = '',
        $hostInfo = '',
        $filename = '',
        $debug = false,
        $logPath = null
    ) {
        $this->logPath = $logPath !== null ? $logPath : self::$LOG_PATH;

        $this->logger = new Logger($this->logPath, self::$LOG_PREFIX, $debug);
        $toolsLogger = new Logger($this->logPath, self::$LOG_PREFIX_TOOLS, $debug);
        $curl = new Curl();
        $this->tools = new Tools($toolsLogger, $curl);

        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
        $this->hostInfo = $hostInfo;
        $this->filename = $filename;
        $this->logEnabled = $debug;

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
    public function Verify($clearCookie = '')
    {
    }

  /**
   * Returns the Download URI to be used by Download Station.
   *
   * @return array|bool
   */
    public function GetDownloadInfo()
    {
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
        foreach (self::$MEDIATHEKEN as $mediathek) {
            $mediathekLogger = new Logger($this->logPath, $mediathek, $this->logEnabled);
            $instance = new $mediathek($mediathekLogger, $this->tools);

            if ($instance->supportsUrl($this->url)) {
                return $instance;
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
}
