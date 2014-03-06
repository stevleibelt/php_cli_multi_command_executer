<?php
/**
 * @author stev leibelt <artodeto@bazzline.net>
 * @since 2014-03-06 
 */

namespace Net\Bazzline\Cli\MultiCommandExecuter;

use Exception;

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
        $options = getopt('c:d', array('config:', 'debug'));

        $configurationFilePath = (isset($options['c']))
            ? $options['c']
            : ((isset($options['config']))
                ? $options['config']
                : null);

        if (is_null($configurationFilePath)) {
            throw new Exception(
                'Usage: ' . $argv[0] . ' -c"path/to/configuration/file.json" [-d]' . PHP_EOL .
                'Usage: ' . $argv[0] . ' --config "path/to/configuration/file.json" [--debug]' . PHP_EOL
            );
        }
        if (!is_file($configurationFilePath)) {
            throw new Exception(
                'Invalid configration file provided: "' . $configurationFilePath . '" does not exist'
            );
        }
        cli_set_process_title('multi command executer - ' . $argv[0]);

        $configuration = (array) json_decode(file_get_contents($configurationFilePath));

        $application = new Application();
        $application->setConfiguration($configuration);

        return $application;
    }
} 