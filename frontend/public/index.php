<?php

use TheiNaD\DSMediatheken\SynoFileHostingMediathek;

require_once __DIR__ . '/../../vendor/autoload.php';

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

include __DIR__ . '/../header.php';
include __DIR__ . '/../start.php';
include __DIR__ . '/../footer.php';
