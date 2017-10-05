<?php
/**
 * Setting up defines for use outside of the Download Station environment.
 */
if (!defined('LOGIN_FAIL')) {
  define('LOGIN_FAIL', 4);
}

if (!defined('USER_IS_FREE')) {
  define('USER_IS_FREE', 5);
}

if (!defined('USER_IS_PREMIUM')) {
  define('USER_IS_PREMIUM', 6);
}

if (!defined('ERR_FILE_NO_EXIST')) {
  define('ERR_FILE_NO_EXIST', 114);
}

if (!defined('ERR_REQUIRED_PREMIUM')) {
  define('ERR_REQUIRED_PREMIUM', 115);
}

if (!defined('ERR_NOT_SUPPORT_TYPE')) {
  define('ERR_NOT_SUPPORT_TYPE', 116);
}

if (!defined('DOWNLOAD_URL')) {
  define('DOWNLOAD_URL', 'DOWNLOAD_URL'); // Real download url
}

if (!defined('DOWNLOAD_FILENAME')) {
  define('DOWNLOAD_FILENAME', 'DOWNLOAD_FILENAME'); // Saved file name
}

if (!defined('DOWNLOAD_STATION_USER_AGENT')) {
  define('DOWNLOAD_STATION_USER_AGENT',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36');
}

if (!defined('DOWNLOAD_COUNT')) {
  define('DOWNLOAD_COUNT', 'count'); // Number of seconds to wait
}

if (!defined('DOWNLOAD_ISQUERYAGAIN')) {
  define('DOWNLOAD_ISQUERYAGAIN',
    'isqueryagain'); // 1: Use the original url query from the user again. 2: Use php output url query again.
}

if (!defined('DOWNLOAD_ISPARALLELDOWNLOAD')) {
  define('DOWNLOAD_ISPARALLELDOWNLOAD', 'isparalleldownload'); //Task can download parallel flag.
}

if (!defined('DOWNLOAD_COOKIE')) {
  define('DOWNLOAD_COOKIE', 'cookiepath');
}
