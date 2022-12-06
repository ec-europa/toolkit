<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Features\Commands {

    use EcEuropa\Toolkit\TaskRunner\Commands\DrupalCommands;
    use EcEuropa\Toolkit\Tests\AbstractTest;
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
         * Data provider for testDrupalSettingsSetup.
         *
         * @return array
         *   An array of test data arrays with assertions.
         */
        public function dataProviderSettings()
        {
            return $this->getFixtureContent('commands/drupal-settings.yml');
        }

        /**
         * Test Toolkit drupal commands.
         *
         * @param array $config
         *   A configuration array.
         * @param mixed $initialDefaultSettings
         *   An initial default settings.
         * @param mixed $initialSettings
         *   An initial settings.
         * @param array $expectations
         *   Test assertions.
         *
         * @dataProvider dataProvider
         */
        public function testDrupalCommands(string $command, array $config, mixed $initialDefaultSettings, mixed $initialSettings, array $expectations)
        {
            // Setup configuration file.
            if (!empty($config)) {
                $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));
            }

            // Setup test directory.
            $root = $config['drupal']['root'] ?? 'web';
            $sitesSubdir = $config['drupal']['site']['sites_subdir'] ?? 'default';
            $settingsRoot = $this->getSandboxRoot() . '/' . $root . '/sites/' . $sitesSubdir;
            mkdir($settingsRoot, 0777, true);

            // Setup initial default.settings.php and settings.php, if any.
            if ($initialDefaultSettings !== null) {
                $this->fs->dumpFile($settingsRoot . '/default.settings.php', $initialDefaultSettings);
            }

            // Setup settings.php file, if test case requires it.
            if ($initialSettings !== null) {
                $this->fs->dumpFile($settingsRoot . '/settings.php', $initialSettings);
            }

            // Run command.
            $result = $this->runCommand($command);
            //$this->debugExpectations($result['output'], $expectations);
            // Assert expectations.
            foreach ($expectations as $expectation) {
                $this->assertDynamic($result['output'], $expectation);
            }
        }

        /**
         * Test Toolkit very own "drupal:settings-setup" command.
         *
         * @param array $config
         *   A configuration array.
         * @param mixed $initialDefaultSettings
         *   An initial default settings.
         * @param mixed $initialSettings
         *   An initial settings.
         * @param array $expectations
         *   Test assertions.
         *
         * @dataProvider dataProviderSettings
         */
//        public function testDrupalCommandsOutputFile(string $command, array $config, mixed $initialDefaultSettings, mixed $initialSettings, array $expectations)
//        {
//            // Setup configuration file.
//            if (!empty($config)) {
//                $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($config));
//            }
//
//            // Setup test directory.
//            $root = $config['drupal']['root'] ?? 'web';
//            $sitesSubdir = $config['drupal']['site']['sites_subdir'] ?? 'default';
//            $settingsRoot = $this->getSandboxRoot() . '/' . $root . '/sites/' . $sitesSubdir;
//            mkdir($settingsRoot, 0777, true);
//
//            // Setup initial default.settings.php and settings.php, if any.
//            file_put_contents($settingsRoot . '/default.settings.php', $initialDefaultSettings);
//
//            // Setup settings.php file, if test case requires it.
//            if ($initialSettings) {
//                file_put_contents($settingsRoot . '/settings.php', $initialSettings);
//            }
//
//            // Run command.
//            $this->runCommand($command, false);
//
//            // Assert expectations.
//            foreach ($expectations as $expectation) {
//                $content = file_get_contents($this->getSandboxFilepath($expectation['file']));
//                $this->assertDynamic($content, $expectation);
//            }
//        }

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
