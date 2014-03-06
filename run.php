#!/bin/php
<?php
/**
 * @author stev leibelt <artodeto@bazzline.net>
 * @since 2014-03-06 
 */

require_once __DIR__ . '/vendor/autoload.php';

try {
    \Net\Bazzline\Cli\MultiCommandExecuter\ApplicationFactory::create()->andRun();
    //application factory
    //setup application
    //execute
} catch (Exception $exception) {
    echo $exception->getMessage() . PHP_EOL;
}