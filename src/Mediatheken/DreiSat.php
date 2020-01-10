<?php

namespace TheiNaD\DSMediatheken\Mediatheken;

/**
 * @author    Daniel Gehn <me@theinad.com>
 * @copyright 2017-2020 Daniel Gehn
 * @license   http://opensource.org/licenses/MIT Licensed under MIT License
 */
class DreiSat extends ZDF
{
    protected static $API_BASE_URL = 'https://api.3sat.de';
    protected static $SUPPORT_MATCHER = '3sat.de';
}
