<?php

namespace OpenEuropa\TaskRunner\Tests\Tasks;

use EcEuropa\Toolkit\Task\Git\loadTasks;
use EcEuropa\Toolkit\Tests\AbstractTaskTest;

/**
 * Test EnsureBranch task.
 *
 * @covers \EcEuropa\Toolkit\Task\Git\EnsureBranch
 */
class EnsureBranchTest extends AbstractTaskTest {

  use loadTasks;
  use \Robo\Task\Filesystem\loadTasks;
  use \Robo\Task\File\loadTasks;
  use \Robo\Task\Vcs\loadTasks;

  /**
   * Test EnsureBranch task.
   */
  public function testEnsureBranch() {
    $this->setupRepository('repo')->run();

    $this->taskEnsureBranch('production')
      ->workingDir($this->getSandboxRoot() . '/repo')
      ->run();

    $this->assertContains('[Vcs\GitStack] git checkout -b production', $this->output->fetch());
  }

  /**
   * Setup a test repository in sandbox directory.
   *
   * @param string $name
   *   Test repository name.
   *
   * @return \Robo\Collection\CollectionBuilder
   *   Collection builder.
   */
  protected function setupRepository($name) {
    return $this->collectionBuilder()
      ->addTaskList([
        $this->taskFilesystemStack()
          ->mkdir($name)
          ->touch("$name/a")
          ->touch("$name/b")
          ->touch("$name/c"),
        $this->taskGitStack()
          ->silent(TRUE)
          ->env('GIT_AUTHOR_NAME', 'John Smith')
          ->env('GIT_COMMITTER_NAME', 'John Smith')
          ->env('GIT_AUTHOR_EMAIL', 'john@smith.com')
          ->env('GIT_COMMITTER_EMAIL', 'john@smith.com')
          ->dir($name)
          ->exec('init')
          ->add('.')
          ->commit('Initial commit.'),
      ]);
  }

}
