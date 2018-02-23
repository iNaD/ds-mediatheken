<div class="result-container">
    <div class="result">
        <h3>Result:</h3>
        DOWNLOAD_URL: <a href="<?php echo $downloadInfo['DOWNLOAD_URL']; ?>" target="_blank"><?php echo $downloadInfo['DOWNLOAD_URL']; ?></a><br>
        DOWNLOAD_FILENAME: <?php echo $downloadInfo['DOWNLOAD_FILENAME']; ?>
    </div>
    <div class="log">
        <h3>Log:</h3>
        <?php foreach ($combinedLog as $logEntry) : ?>
            <p>
                [<?php echo $logEntry['formattedDate']; ?>] [<?php echo $logEntry['logger']; ?>]: <?php echo $logEntry['message']; ?>
            </p>
        <?php endforeach; ?>
    </div>
</div>
