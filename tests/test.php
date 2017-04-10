<?php

require_once dirname(__FILE__) . '/../src/SynoFileHostingMediathek.php';

$mediathek = new SynoFileHostingMediathek(urldecode($_GET['url']),
  '',
  '',
  '',
  '',
  true,
  dirname(__FILE__) . '/mediathek.log');

var_dump($mediathek->GetDownloadInfo());
