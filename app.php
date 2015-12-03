#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Console\Command\PickCommand;
use Symfony\Component\Console\Application;

date_default_timezone_set('UTC');

$application = new Application();
$application->add(new PickCommand());
$application->run();
