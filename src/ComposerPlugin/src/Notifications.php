<?php

declare(strict_types=1);

namespace Toolkit\ComposerPlugin;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Composer\Util\ProcessExecutor;

/**
 * Toolkit plugin to print notifications.
 */
class Notifications implements PluginInterface, EventSubscriberInterface
{

    /**
     * The Composer object.
     *
     * @var \Composer\Composer
     */
    protected $composer;

    /**
     * The InputOutput interface.
     *
     * @var \Composer\IO\IOInterface
     */
    protected $io;

    /**
     * The Process helper.
     *
     * @var \Composer\Util\ProcessExecutor
     */
    protected $processExecutor;

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->processExecutor = new ProcessExecutor($io);
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'show',
            ScriptEvents::POST_UPDATE_CMD => 'show',
        ];
    }

    /**
     * Print the Toolkit notifications.
     */
    public function show()
    {
        $binDir = $this->composer->getConfig()->get('bin-dir') ?? 'vendor/bin';
        $output = '';
        $this->processExecutor->execute("$binDir/run toolkit:notifications", $output);
        if (!empty($output)) {
            $this->io->write($output);
        }
    }

}
