<?php

namespace Tests;

// phpcs:disable
require_once __DIR__ . '/../src/Utils/defines.php';

// phpcs:enable

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base TestCase which provides some bootstrapping and utility.
 *
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2018-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
class TestCase extends BaseTestCase
{
    /**
     * @param string $path
     *
     * @return false|string
     */
    protected function getFixture(string $path)
    {
        return file_get_contents(__DIR__ . '/fixtures/' . $path);
    }
}
