<?php

declare(strict_types = 1);

namespace EcEuropa\Toolkit\Tests\Commands {
  use EcEuropa\Toolkit\Tests\AbstractTest;
  use OpenEuropa\TaskRunner\TaskRunner;
  use Symfony\Component\Console\Input\StringInput;
  use Symfony\Component\Console\Output\BufferedOutput;
  use Symfony\Component\Yaml\Yaml;

  /**
   * Test Toolkit Drupal commands.
   */
  class DrupalCommandsTest extends AbstractTest {

    /**
     * Data provider for testDrupalSettingsSetup.
     *
     * @return array
     *   An array of test data arrays with assertations.
     */
    public function dataProvider() {
      return $this->getFixtureContent('commands/drupal-settings-setup.yml');
    }

    /**
     * Test Toolkit very own "drupal:settings-setup" command.
     *
     * @param array $config
     *   A configuration array.
     * @param mixed $initial_default_settings
     *   A initial default settings.
     * @param mixed $initial_settings
     *   A initial settings.
     * @param array $expected
     *   Test assertions.
     *
     * @dataProvider dataProvider
     */
    public function testDrupalSettingsSetup(array $config, $initial_default_settings, $initial_settings, array $expected) {
      // Setup test Task Runner configuration file.
      $configFile = $this->getSandboxFilepath('runner.yml');
      file_put_contents($configFile, Yaml::dump($config));

      // Setup test directory.
      $sites_subdir = isset($config['drupal']['site']['sites_subdir']) ? $config['drupal']['site']['sites_subdir'] : 'default';
      $settings_root = $this->getSandboxRoot() . '/build/sites/default';
      mkdir($settings_root, 0777, TRUE);

      // Setup initial default.settings.php and settings.php, if any.
      file_put_contents($settings_root . '/default.settings.php', $initial_default_settings);

      // Setup settings.php file, if test case requires it.
      if ($initial_settings) {
        file_put_contents($settings_root . '/settings.php', $initial_settings);
      }

      // Run command.
      $input = new StringInput('drupal:settings-setup --working-dir=' . $this->getSandboxRoot());
      $runner = new TaskRunner($input, new BufferedOutput(), $this->getClassLoader());
      $runner->run();

      // Assert expectations.
      foreach ($expected as $row) {
        $content = file_get_contents($this->getSandboxFilepath($row['file']));
        $this->assertContainsNotContains($content, $row);
      }
    }

  }
}

namespace EcEuropa\Toolkit\TaskRunner\Commands {

  /**
   * Override random_bytes function for test.
   */
  function random_bytes() {
    return 'abc';
  }

}
