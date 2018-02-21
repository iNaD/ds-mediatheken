<?php

require_once dirname(__FILE__) . '/../vendor/autoload.php';

$url = isset($_GET['url']) ? trim($_GET['url']) : null;

if ($url !== null && count($url) > 0) {
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

    var_dump($mediathek->GetDownloadInfo());
    var_dump($mediathek->getCombinedLog());
}
?>

<form action="index.php" method="GET">
    <input type="text" name="url" placeholder="URL" value="<?php echo $url; ?>">
    <button type="submit">Go</button>
</form>
