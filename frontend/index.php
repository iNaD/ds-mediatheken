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
        dirname(__FILE__) . '/mediathek.log'
    );

    var_dump($mediathek->GetDownloadInfo());
}
?>

<form action="index.php" method="GET">
    <input type="text" name="url" placeholder="URL" value="<?php echo $url; ?>">
    <button type="submit">Go</button>
</form>
