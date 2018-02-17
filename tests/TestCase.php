<?php
require_once dirname(__FILE__) . '/../src/utils/defines.php';

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
  protected function getFixture(string $path)
  {
    return file_get_contents(dirname(__FILE__) . '/fixtures/' . $path);
  }
}
