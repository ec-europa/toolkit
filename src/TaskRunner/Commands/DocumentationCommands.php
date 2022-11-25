<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

use EcEuropa\Toolkit\TaskRunner\AbstractCommands;
use EcEuropa\Toolkit\Toolkit;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\ResultData;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;

class DocumentationCommands extends AbstractCommands
{

    /**
     * The GitHub token.
     *
     * @var string
     */
    private string $token;

    /**
     * The documentation directory.
     *
     * @var string
     */
    private string $docsDir;

    /**
     * A temporary directory.
     *
     * @var string
     */
    private string $tmpDir;

    /**
     * The repository where the documentation is.
     *
     * @var string
     */
    private string $repo;

    /**
     * The documentation branch.
     *
     * @var string
     */
    private string $branch;

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return Toolkit::getToolkitRoot() . '/config/commands/documentation.yml';
    }

    /**
     * Generate the documentation.
     *
     * @command toolkit:generate-documentation
     *
     * @option token    The GitHub token to use.
     * @option repo     The repository.
     * @option docs-dir The documentation directory.
     * @option tmp-dir  The temporary directory.
     * @option branch   The documentation branch.
     *
     * @hidden
     *
     * @aliases tk-docs
     */
    public function toolkitGenerateDocumentation(ConsoleIO $io, array $options = [
        'token' => InputOption::VALUE_REQUIRED,
        'repo' => InputOption::VALUE_REQUIRED,
        'docs-dir' => InputOption::VALUE_REQUIRED,
        'tmp-dir' => InputOption::VALUE_REQUIRED,
        'branch' => InputOption::VALUE_REQUIRED,
    ])
    {
        if (empty($options['token']) || $options['token'] === '${env.GITHUB_API_TOKEN}') {
            $io->error('The env var GITHUB_API_TOKEN is required.');
            return ResultData::EXITCODE_ERROR;
        }

        if (!$this->downloadPhpDocPhar()) {
            $io->error('Fail to download the phpDocumentor.phar file.');
            return ResultData::EXITCODE_ERROR;
        }

        $this->token = $options['token'];
        $this->repo = $options['repo'];
        $this->docsDir = $options['docs-dir'];
        $this->tmpDir = $options['tmp-dir'];
        $this->branch = $options['branch'];
        $builder = $this->collectionBuilder();

        if (file_exists($this->tmpDir)) {
            $builder->addTask($this->taskDeleteDir($this->tmpDir));
        }

        return $builder
            // Backup all .rst files.
            ->addTask($this->backupRelevantFiles())
            // Clean up documentation folder.
            ->addTask($this->cleanDir($this->docsDir))
            // Restore stored files.
            ->addTask($this->taskCopyDir([$this->tmpDir => $this->docsDir]))
            // Generate documentation.
            ->addTask($this->taskExec($this->getBin('phpDoc')))
            // Clean up temporary folder.
            ->addTask($this->cleanDir($this->tmpDir))
            // Clone documentation.
            ->addTaskList($this->gitClone())
            // Clean up before copy new content.
            ->addTask($this->cleanDir($this->tmpDir, false))
            // Copy generated docs.
            ->addTask($this->taskCopyDir([$this->docsDir => $this->tmpDir]))
            // Clean up all .rst files.
            ->addTask($this->cleanUpRstFiles())
            // Commit and push.
            ->addTask($this->gitAddCommitPush())
            // Delete temporary folder.
            ->addTask($this->taskDeleteDir($this->tmpDir));
    }

    /**
     * Generate the list of commands in the commands.rst file.
     *
     * @command toolkit:generate-commands-list
     *
     * @hidden
     *
     * @aliases tk-gcl
     */
    public function toolkitGenerateCommandsList()
    {
        // Get the available commands.
        $commands = $this->taskExec($this->getBin('run'))
            ->silent(true)->run()->getMessage();
        // Remove the header part.
        $commands = preg_replace('/((.|\n)*)(Available commands:)/', '\3', $commands);
        // Add spaces to match the .rst format.
        $commands = preg_replace('/^/im', ' ', $commands);

        $start = ".. toolkit-block-commands\n\n.. code-block::\n\n";
        $end = "\n\n.. toolkit-block-commands-end";
        $task = $this->taskReplaceBlock('docs/guide/commands.rst')
            ->start($start)->end($end)->content($commands);
        return $this->collectionBuilder()->addTask($task);
    }

    /**
     * Backup all *.rst files.
     */
    private function backupRelevantFiles()
    {
        $task = $this->taskFilesystemStack();
        if (!file_exists($this->tmpDir)) {
            $task->mkdir($this->tmpDir);
        }
        $finder = new Finder();
        $finder->files()->in($this->docsDir)->name('*.rst')->sortByName(true);
        foreach ($finder as $file) {
            $task->copy($file->getPathname(), $this->tmpDir . '/' . $file->getRelativePathname());
        }
        return $task;
    }

    /**
     * Clone the documentation branch (hide output to avoid expose token).
     */
    private function gitClone(): array
    {
        $tasks = [];
        $repo = sprintf($this->repo, $this->token);
        $tasks[] = $this->collectionBuilder()->addCode(function () use ($repo) {
            // Remove the token from the url for output.
            if (str_contains($repo, '@')) {
                $protocol = strstr($repo, '://', true);
                $repo = $protocol . '://' . substr(strstr($repo, '@'), 1);
            }
            $msg = sprintf(
                " <fg=white;bg=cyan;options=bold>[Vcs\GitStack]</> Running <info>git clone --depth 1 %s %s --branch %s</>",
                $repo,
                $this->tmpDir,
                $this->branch
            );
            $this->output()->writeln(['', $msg]);
        });
        $tasks[] = $this->taskGitStack()
            ->cloneShallow($repo, $this->tmpDir, $this->branch)
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG);
        return $tasks;
    }

    /**
     * Git add commit and push to the documentation branch.
     */
    private function gitAddCommitPush()
    {
        return $this->taskExecStack()
            ->stopOnFail()
            ->exec('git -C ' . $this->tmpDir . ' config user.name "Toolkit"')
            ->exec('git -C ' . $this->tmpDir . ' config user.email "DIGIT-NEXTEUROPA-QA@ec.europa.eu"')
            ->exec('git -C ' . $this->tmpDir . ' add .')
            ->exec('git -C ' . $this->tmpDir . ' commit -m "Generate documentation."')
            ->exec('git -C ' . $this->tmpDir . ' push');
    }

    /**
     * Clean up given directory.
     *
     * @param string $directory
     *   The directory to clean
     * @param bool $includeHidden
     *   If true, all hidden files will be removed.
     */
    private function cleanDir(string $directory, bool $includeHidden = true)
    {
        if ($includeHidden) {
            // The task taskCleanDir() removes the .git/ folder and hidden files.
            return $this->taskCleanDir($directory);
        }
        // This glob do not include hidden files or directories.
        return $this->taskFilesystemStack()->remove(glob($directory . '/*'));
    }

    /**
     * Clean up documentation to keep only .html files.
     */
    private function cleanUpRstFiles()
    {
        // Note, use Finder inside a addCode() because Finder will search
        // immediately for the files and the folder do not exist yet.
        return $this->collectionBuilder()->addCode(function () {
            $finder = new Finder();
            $finder->files()->in($this->tmpDir)->name('*.rst');
            foreach ($finder as $file) {
                $this->_remove($file->getPathname());
            }
        });
    }

    /**
     * Ensure that the phpDoc phar file exists.
     */
    private function downloadPhpDocPhar(): bool
    {
        $phpDoc = $this->getBinPath('phpDoc');
        if (!file_exists($phpDoc)) {
            try {
                file_put_contents($phpDoc, file_get_contents('https://phpdoc.org/phpDocumentor.phar'));
            } catch (\Exception $e) {
                return false;
            }
            if (filesize($phpDoc) <= 0) {
                return false;
            }
            $this->_chmod($phpDoc, 0755);
        }
        return true;
    }

}
