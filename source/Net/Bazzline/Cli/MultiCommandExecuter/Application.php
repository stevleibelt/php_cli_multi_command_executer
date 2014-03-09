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
     * @var boolean
     * @author stev leibelt <artodeto@bazzline>
     * @since 2013-03-09
     */
    private $beVerbose = false;

    /**
     * @var array
     * @author stev leibelt <artodeto@bazzline>
     * @since 2014-03-07
     */
    private $configuration;

    /**
     * @return $this
     * @author stev leibelt <artodeto@bazzline.net>
     * @since 2014-03-09
     */
    public function beVerbose()
    {
        $this->beVerbose = true;

        return $this;
    }

    /**file
     * @param array $configuration
     * @return $this
     * @throws Exception
     * @author stev leibelt <artodeto@bazzline.net>
     * @since 2014-03-07
     */
    public function setConfiguration(array $configuration)
    {
        $this->validateConfiguration($configuration);
        $this->configuration = $configuration;

        return $this;
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
            $this->outputCurrentPathsProgress($path);
            $currentDirectoryPath = $currentWorkingDirectory . DIRECTORY_SEPARATOR . $path;

            if (!is_dir($currentDirectoryPath)) {
                throw new Exception(
                    'Invalid path provided: "' . $currentDirectoryPath . '" does not exist'
                );
            }

            chdir($currentDirectoryPath);

            foreach ($this->configuration['commands'] as $command) {
                $escapedCommand = escapeshellcmd($command);
                $this->outputCurrentCommandProgress($command);
                system($escapedCommand);
            }
        }
    }

    /**
     * @param $command
     * @author stev leibelt <artodeto@bazzline.net>
     * @since 2014-03-09
     */
    private function outputCurrentCommandProgress($command)
    {
        echo ($this->beVerbose) ? 'executing command: ' . $command . PHP_EOL : '.';
    }

    /**
     * @param $path
     * @author stev leibelt <artodeto@bazzline.net>
     * @since 2014-03-09
     */
    private function outputCurrentPathsProgress($path)
    {
        echo ($this->beVerbose) ? 'cd to path: ' . $path . PHP_EOL : '.';
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