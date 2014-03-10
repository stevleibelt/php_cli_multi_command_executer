#!/bin/php
<?php
/**
 * @author stev leibelt <artodeto@bazzline.net>
 * @since 2014-03-06 
 */

require_once __DIR__ . '/vendor/autoload.php';

use Net\Bazzline\Cli\MultiCommandExecuter\Application;
use Net\Bazzline\Cli\MultiCommandExecuter\ApplicationFactory;

try {
    $application = ApplicationFactory::create();
    if ($application instanceof Application) {
        $application->andRun();
    }
} catch (Exception $exception) {
    echo $exception->getMessage() . PHP_EOL;
}
