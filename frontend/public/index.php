<?php

use TheiNaD\DSMediatheken\SynoFileHostingMediathek;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

$url = isset($_GET['url']) ? trim($_GET['url']) : null;
$downloadInfo = null;
$combinedLog = null;

if ($url !== null && strlen($url) > 0) {
    $mediathek = new SynoFileHostingMediathek(
        urldecode($url),
        '',
        '',
        '',
        '',
        true,
        null,
        false
    );

    $downloadInfo = $mediathek->GetDownloadInfo();
    $combinedLog = $mediathek->getCombinedLog();
}

include dirname(__FILE__) . '/../header.php';
include dirname(__FILE__) . '/../start.php';
include dirname(__FILE__) . '/../footer.php';
