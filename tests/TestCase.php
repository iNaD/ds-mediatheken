<?php

namespace Tests;

// phpcs:disable
require_once dirname(__FILE__) . '/../src/Utils/defines.php';

// phpcs:enable

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base TestCase which provides some bootstrapping and utility.
 *
 * @author Daniel Gehn <me@theinad.com>
 * @copyright 2018 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class TestCase extends BaseTestCase
{
    protected function getFixture(string $path)
    {
        return file_get_contents(dirname(__FILE__) . '/fixtures/' . $path);
    }
}
