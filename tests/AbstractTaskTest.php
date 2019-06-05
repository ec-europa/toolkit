<?php

namespace EcEuropa\Toolkit\Tests;

use OpenEuropa\TaskRunner\TaskRunner;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Tasks;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Robo\TaskAccessor;
use Robo\Collection\CollectionBuilder;

/**
 * Abstract task testing class, to be extended by task tests.
 */
abstract class AbstractTaskTest extends AbstractTest implements ContainerAwareInterface {

  use TaskAccessor;
  use ContainerAwareTrait;

  /**
   * Output buffer.
   *
   * @var \Symfony\Component\Console\Output\BufferedOutput
   */
  protected $output;

  /**
   * Setup hook.
   */
  public function setUp() {
    $this->output = new BufferedOutput();
    $runner = new TaskRunner(new StringInput(''), $this->output, $this->getClassLoader());
    $this->setContainer($runner->getContainer());

    parent::setUp();
  }

  /**
   * Construct and return test collection builder.
   *
   * @return \Robo\Collection\CollectionBuilder
   *   Test collection builder.
   */
  public function collectionBuilder() {
    return CollectionBuilder::create($this->getContainer(), new Tasks());
  }

}
