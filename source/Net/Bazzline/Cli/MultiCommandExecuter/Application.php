<?php
/**
 * @author stev leibelt <artodeto@bazzline.net>
 * @since 2014-03-06 
 */

namespace Net\Bazzline\Cli\MultiCommandExecuter;

use Exception;

/**
 * Class Application
 * @package Net\Bazzline\Cli\MultiCommandExecuter
 * @author stev leibelt <artodeto@bazzline.net>
 * @since 2014-03-07
 */
class Application
{
    /**
     * @var array
     * @author stev leibelt <artodeto@bazzline>
     * @since 2014-03-07
     */
    private $configuration;

    /**
     * @param array $configuration
     * @throws Exception
     * @author stev leibelt <artodeto@bazzline.net>
     * @since 2014-03-07
     */
    public function setConfiguration(array $configuration)
    {
        $this->validateConfiguration($configuration);
        $this->configuration = $configuration;
    }

    /**
     * @throws Exception
     * @author stev leibelt <artodeto@bazzline.net>
     * @since 2014-03-07
     */
    public function andRun()
    {
        $currentWorkingDirectory = getcwd();
        foreach ($this->configuration['paths'] as $path) {
            $currentDirectoryPath = $currentWorkingDirectory . DIRECTORY_SEPARATOR . $path;

            if (!is_dir($currentDirectoryPath)) {
                throw new Exception(
                    'Invalid path provided: "' . $currentDirectoryPath . '" does not exist'
                );
            }

            //debug echo PHP_EOL . var_export($currentDirectoryPath, true) . PHP_EOL;
            chdir($currentDirectoryPath);

            foreach ($this->configuration['commands'] as $command) {
                $escapedCommand = escapeshellcmd($command);
                //debug echo command
                system($escapedCommand);
            }
        }
    }

    /**
     * @param array $configuration
     * @throws \Exception
     * @author stev leibelt <artodeto@bazzline.net>
     * @since 2014-03-07
     */
    private function validateConfiguration(array $configuration)
    {
        if ((!isset($configuration['paths']))
            || (!is_array($configuration['paths']))
            || (count($configuration['paths']) < 1)) {
            throw new Exception(
                'Invalid configuration provided. "paths" has to be an array with at least one entry'
            );
        }

        if ((!isset($configuration['commands']))
            || (!is_array($configuration['commands']))
            || (count($configuration['commands']) < 1)) {
            throw new Exception(
                'Invalid configuration provided. "commands" has to be an array with at least one entry'
            );
        }
    }
} 