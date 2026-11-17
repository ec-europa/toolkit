<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Hooks;

use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\Hooks\HookManager;
use Consolidation\AnnotatedCommand\Hooks\ValidatorInterface;
use EcEuropa\Toolkit\TaskRunner\Commands\ConfigurationCommands;
use EcEuropa\Toolkit\Toolkit;

/**
 * Validates if a command is overridden.
 */
final class ProtectedCommandGuard implements ValidatorInterface
{

    /**
     * The list of protected command names.
     *
     * @var string[]
     */
    private array $protectedCommands;

    /**
     * The hook manager.
     *
     * @var \Consolidation\AnnotatedCommand\Hooks\HookManager
     */
    private HookManager $hookManager;

    /**
     * Constructs a new ProtectedCommandGuard.
     *
     * @param string[] $protectedCommands
     *   The list of commands to protect.
     * @param \Consolidation\AnnotatedCommand\Hooks\HookManager $hookManager
     *   The Hook manager.
     */
    public function __construct(array $protectedCommands, HookManager $hookManager)
    {
        $this->protectedCommands = $protectedCommands;
        $this->hookManager = $hookManager;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate(CommandData $commandData): void
    {
        $inputCommand = (string) $commandData->input()->getArgument('command');
        $annotatedCommand = (string) $commandData->annotationData()->get('command');

        // Determine which protected command (if any) is being invoked.
        $matchedCommand = null;
        foreach ([$inputCommand, $annotatedCommand] as $candidate) {
            if ($candidate !== '' && in_array($candidate, $this->protectedCommands, true)) {
                $matchedCommand = $candidate;
                break;
            }
        }

        if ($matchedCommand === null) {
            return;
        }

        // Block @hook replace-command hooks outside EcEuropa\Toolkit\ namespace.
        $hookKeys = array_unique([$matchedCommand, str_replace(':', '.', $matchedCommand)]);
        foreach ($hookKeys as $key) {
            $replaceHooks = $this->hookManager->getHooks($key, [HookManager::REPLACE_COMMAND_HOOK]);

            foreach ($replaceHooks as $hook) {
                $target = $hook[0] ?? $hook;
                $ownerClass = match (true) {
                    is_object($target) => $target::class,
                    is_string($target) => $target,
                    default => 'unknown',
                };

                if (!str_starts_with($ownerClass, 'EcEuropa\\Toolkit\\')) {
                    throw new \Exception(sprintf(
                        'Command "%s" is protected by %s and cannot be overridden (found @hook replace-command in %s).',
                        $matchedCommand,
                        Toolkit::REPOSITORY,
                        $ownerClass
                    ));
                }
            }
        }

        // Block overrides via runner.yml ('commands:' section).
        $ownerClass = $commandData->annotationData()->get('_classname');

        if ($ownerClass === ConfigurationCommands::class) {
            throw new \Exception(sprintf(
                'Command "%s" is protected by %s and cannot be overridden via runner.yml.',
                $matchedCommand,
                Toolkit::REPOSITORY
            ));
        }

        // Block overrides or duplicate @command definitions.
        if ($ownerClass && !str_starts_with($ownerClass, 'EcEuropa\\Toolkit\\')) {
            throw new \Exception(sprintf(
                'Command "%s" is protected by %s and cannot be overridden (redefined in %s).',
                $matchedCommand,
                Toolkit::REPOSITORY,
                $ownerClass
            ));
        }
    }

}
