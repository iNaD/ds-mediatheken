<?php

require_once dirname(__FILE__) . '/../src/SynoFileHostingMediathek.php';

$url = isset($_GET['url']) ? $_GET['url'] : null;

if ($url === null) {
  die('URL is empty');
}

$mediathek = new SynoFileHostingMediathek(urldecode($url),
  '',
  '',
  '',
  '',
  true,
  dirname(__FILE__) . '/mediathek.log');

var_dump($mediathek->GetDownloadInfo());
