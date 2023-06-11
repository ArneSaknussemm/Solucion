<?php

if ( ! defined('ABSPATH') ) {
    die('Direct access not permitted.');
}

require_once(dirname(__FILE__).'/functions/core.php');
require_once(dirname(__FILE__).'/functions/cron.php');
require_once(dirname(__FILE__).'/functions/findings.php');
require_once(dirname(__FILE__).'/functions/admin_list.php');

foreach ( glob( (__DIR__) . '/hooks/*.php' ) as $filename ) {
    require_once $filename;
}

foreach ( glob( (__DIR__) . '/data/*.php' ) as $filename ) {
    require_once $filename;
}
