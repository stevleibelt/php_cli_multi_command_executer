<?php
/**
 * @author stev leibelt <artodeto@bazzline.net>
 * @since 2014-03-06 
 */

namespace Net\Bazzline\Cli\MultiCommandExecuter;

use Exception;
use Net\Bazzline\Component\Lock\LockAwareInterface;
use Net\Bazzline\Component\Lock\LockInterface;
use Net\Bazzline\Component\Shutdown\ShutdownAwareInterface;
use Net\Bazzline\Component\Shutdown\ShutdownInterface;
use RuntimeException;

/**
 * Class Application
 * @package Net\Bazzline\Cli\MultiCommandExecuter
 * @author stev leibelt <artodeto@bazzline.net>
 * @since 2014-03-07
 */
class Application implements LockAwareInterface, ShutdownAwareInterface
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
     * @var LockInterface
     * @author stev leibelt <artodeto@bazzline>
     * @since 2014-03-07
     */
    private $lock;

    /**
     * @var ShutdownInterface
     * @author stev leibelt <artodeto@bazzline>
     * @since 2014-03-09
     */
    private $shutdown;

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
     * Set Lock
     *
     * @param LockInterface $lock
     * @author stev leibelt <artodeto@arcor.de>
     * @since 2013-06-30
     */
    public function setLock(LockInterface $lock)
    {
        $this->lock = $lock;
    }

    /**
     * Get Lock
     *
     * @return LockInterface
     * @author stev leibelt <artodeto@arcor.de>
     * @since 2013-06-30
     */
    public function getLock()
    {
        return $this->lock;
    }

    /**
     * Set shutdown
     *
     * @param ShutdownInterface $shutdown
     */
    public function setShutdown(ShutdownInterface $shutdown)
    {
        $this->shutdown = $shutdown;
    }

    /**
     * Get shutdown
     *
     * @return ShutdownInterface
     */
    public function getShutdown()
    {
        return $this->shutdown;
    }

    /**
     * @throws Exception
     * @author stev leibelt <artodeto@bazzline.net>
     * @since 2014-03-07
     */
    public function andRun()
    {
        try {
            $this->acquireLock();
        } catch (RuntimeException $exception) {
            throw new Exception($exception->getMessage());
        }

        try {
            $this->workOnPaths();
            $this->releaseLock();
        } catch (RuntimeException $exception) {
            $this->releaseLock();

            throw new Exception($exception->getMessage());
        }
    }

    /**
     * @throws \RuntimeException
     * @author stev leibelt <artodeto@bazzline.net>
     * @since 2014-03-09
     */
    private function workOnPaths()
    {
        $currentWorkingDirectory = getcwd();

        foreach ($this->configuration['paths'] as $path) {
            if ($this->shutdownIsRequested()) {
                $this->outputShutdown();
                break;
            }
            $this->outputCurrentPathsProgress($path);
            $currentDirectoryPath = $currentWorkingDirectory . DIRECTORY_SEPARATOR . $path;

            if (!is_dir($currentDirectoryPath)) {
                throw new RuntimeException(
                    'Invalid path provided: "' . $currentDirectoryPath . '" does not exist'
                );
            }

            chdir($currentDirectoryPath);
            $this->workOnCommands();
        }
    }

    /**
     * @author stev leibelt <artodeto@bazzline.net>
     * @since 2014-03-09
     */
    private function workOnCommands()
    {
        foreach ($this->configuration['commands'] as $command) {
            if ($this->shutdownIsRequested()) {
                $this->outputShutdown();
                break;
            }
            $escapedCommand = escapeshellcmd($command);
            $this->outputCurrentCommandProgress($command);
            system($escapedCommand);
        }
    }

    /**
     * @throws \RuntimeException
     * @author stev leibelt <artodeto@bazzline.net>
     * @since 2014-03-09
     */
    private function acquireLock()
    {
        if ($this->lock instanceof LockInterface) {
            $this->lock->acquire();
        }
    }

    /**
     * @throws \RuntimeException
     * @author stev leibelt <artodeto@bazzline.net>
     * @since 2014-03-09
     */
    private function releaseLock()
    {
        if ($this->lock instanceof LockInterface) {
            $this->lock->release();
        }
    }

    /**
     * @return bool
     * @author stev leibelt <artodeto@bazzline.net>
     * @since 2014-03-09
     */
    private function shutdownIsRequested()
    {
        return ($this->shutdown instanceof ShutdownInterface)
            ? $this->shutdown->isRequested() : false;
    }

    /**
     * @author stev leibelt <artodeto@bazzline.net>
     * @since 2014-03-09
     */
    private function outputShutdown()
    {
        if ($this->beVerbose) {
            echo 'shutdown requested' . PHP_EOL;
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