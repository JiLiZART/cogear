#!/usr/bin/php5
<?php
set_time_limit(0);
$settings = parse_ini_file(DIRNAME(__FILE__).'/gears/global/global.info',TRUE);
file_get_contents('http://'.$settings['url'].'/cron/');