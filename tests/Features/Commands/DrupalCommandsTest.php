<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Features\Commands {

    use EcEuropa\Toolkit\TaskRunner\Commands\DrupalCommands;
    use EcEuropa\Toolkit\TaskRunner\Runner;
    use EcEuropa\Toolkit\Tests\AbstractTest;
    use Symfony\Component\Console\Input\StringInput;
    use Symfony\Component\Console\Output\BufferedOutput;
    use Symfony\Component\Yaml\Yaml;

    /**
     * Test Toolkit Drupal commands.
     *
     * @group drupal
     */
    class DrupalCommandsTest extends AbstractTest
    {

        /**
         * Data provider for testDrupalSettingsSetup.
         *
         * @return array
         *   An array of test data arrays with assertions.
         */
        public function dataProvider()
        {
            return $this->getFixtureContent('commands/drupal.yml');
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
        public function testDrupalSettingsSetup(array $config, $initial_default_settings, $initial_settings, array $expected)
        {
            // Setup configuration file.
            if (!empty($config)) {
                $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));
            }

            // Setup test directory.
            $root = $config['drupal']['root'] ?? 'web';
            $sites_subdir = $config['drupal']['site']['sites_subdir'] ?? 'default';
            $settings_root = $this->getSandboxRoot() . '/' . $root . '/sites/' . $sites_subdir;
            mkdir($settings_root, 0777, true);

            // Setup initial default.settings.php and settings.php, if any.
            file_put_contents($settings_root . '/default.settings.php', $initial_default_settings);

            // Setup settings.php file, if test case requires it.
            if ($initial_settings) {
                file_put_contents($settings_root . '/settings.php', $initial_settings);
            }

            // Run command.
            $input = new StringInput('drupal:settings-setup --working-dir=' . $this->getSandboxRoot());
            $runner = new Runner($this->getClassLoader(), $input, new BufferedOutput());
            $runner->run();

            // Assert expectations.
            foreach ($expected as $row) {
                $content = file_get_contents($this->getSandboxFilepath($row['file']));
                $this->assertContainsNotContains($content, $row);
            }
        }

        public function testConfigurationFileExists()
        {
            $this->assertFileExists((new DrupalCommands())->getConfigurationFile());
        }

    }
}

namespace EcEuropa\Toolkit\TaskRunner\Commands {

    /**
     * Override random_bytes function for test.
     *
     * phpcs:disable Generic.NamingConventions.CamelCapsFunctionName.NotCamelCaps
     */
    function random_bytes()
    {
        return 'abc';
    }

}
