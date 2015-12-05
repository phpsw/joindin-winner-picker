#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Console\PickerApplication;

date_default_timezone_set('UTC');

$application = new PickerApplication();
$application->run();
