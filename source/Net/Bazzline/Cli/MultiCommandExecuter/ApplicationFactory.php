<?php
/**
 * @author stev leibelt <artodeto@bazzline.net>
 * @since 2014-03-06 
 */

namespace Net\Bazzline\Cli\MultiCommandExecuter;

use Exception;
use Net\Bazzline\Component\Lock\FileLock;
use Net\Bazzline\Component\Shutdown\FileShutdown;

/**
 * Class ApplicationFactory
 * @package Net\Bazzline\Cli\MultiCommandExecuter
 * @author stev leibelt <artodeto@bazzline.net>
 * @since 2014-03-07
 */
class ApplicationFactory
{
    /**
     * @return Application
     * @throws \Exception
     * @author stev leibelt <artodeto@bazzline.net>
     * @since 2014-03-07
     */
    public static function create()
    {
        global $argv;

        if (!defined('STDIN')) {
            throw new Exception(
                'This script can run on command line only'
            );
        }
        $options = getopt('c:rsv', array('config:', "removeLock", "setShutdown", 'verbose'));

        $processName = $argv[0];
        $currentWorkingDirectory = getcwd();
        $removeLock = (isset($options['l'])) ? true : (isset($options['removeLock']));
        $setShutdown = (isset($options['s'])) ? true : (isset($options['setShutdown']));

        $lock = new FileLock($currentWorkingDirectory . DIRECTORY_SEPARATOR . $processName);
        $shutdown = new FileShutdown($currentWorkingDirectory . DIRECTORY_SEPARATOR . $processName);

        if ($removeLock) {
            $lock->release();

            return null;
        }
        if ($setShutdown) {
            $shutdown->request();

            return null;
        }

        $configurationFilePath = (isset($options['c']))
            ? $options['c']
            : ((isset($options['config']))
                ? $options['config']
                : null);

        if (is_null($configurationFilePath)) {
            throw new Exception(
                'Usage: ' . $argv[0] . ' -c"path/to/configuration/file.json" [-r] [-s] [-v]' . PHP_EOL .
                'Usage: ' . $argv[0] . ' --config "path/to/configuration/file.json" [--removeLock] [--setShutdown] [--verbose]' . PHP_EOL
            );
        }
        if (!is_file($configurationFilePath)) {
            throw new Exception(
                'Invalid configration file provided: "' . $configurationFilePath . '" does not exist'
            );
        }
        if ((version_compare(PHP_VERSION,'5.5.0') >= 0)) {
            cli_set_process_title('multi command executer - ' . $processName);
        }

        $configuration = (array) json_decode(file_get_contents($configurationFilePath));

        $application = new Application();

        $application->setConfiguration($configuration);
        $application->setLock($lock);
        $application->setShutdown($shutdown);

        return $application;
    }
} 
