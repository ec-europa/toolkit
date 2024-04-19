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
        public static function dataProvider()
        {
            return self::getFixtureContent('commands/drupal.yml');
        }

        /**
         * Data provider for testDrupalSettingsSetup.
         *
         * @return array
         *   An array of test data arrays with assertions.
         */
        public static function dataProviderSettings()
        {
            return self::getFixtureContent('commands/drupal-settings-setup.yml');
        }

        /**
         * Test Toolkit drupal commands.
         *
         * @param string $command
         *   A command.
         * @param array $configuration
         *   A configuration array.
         * @param string|null $tokens
         *   Tokens to set in the commit message.
         * @param array $resources
         *   Resources needed for the test.
         * @param array $expectations
         *   Test assertions.
         *
         * @dataProvider dataProvider
         */
        public function testDrupalCommands(string $command, array $configuration = [], string $tokens = null, array $resources = [], array $expectations = [])
        {
            // Setup configuration file.
            if (!empty($configuration)) {
                $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($configuration));
            }

            if ($tokens !== null) {
                putenv('CI_COMMIT_MESSAGE="' . $tokens . '"');
            }

            $this->prepareResources($resources);

            // Run command.
            $result = $this->runCommand($command);
            $this->debugExpectations($result['output'], $expectations);
            // Assert expectations.
            foreach ($expectations as $expectation) {
                $this->assertDynamic($result['output'], $expectation);
            }
        }

        /**
         * Test Toolkit very own "drupal:settings-setup" command.
         *
         * @param string $command
         *   A command.
         * @param array $configuration
         *   A configuration array.
         * @param array $resources
         *   Resources needed for the test.
         * @param array $expectations
         *   Test assertions.
         *
         * @dataProvider dataProviderSettings
         */
        public function testDrupalSettingsSetupCommands(string $command, array $configuration, array $resources = [], array $expectations = [])
        {
            // Setup configuration file.
            if (!empty($configuration)) {
                $this->fs->dumpFile($this->getSandboxFilepath('runner.yml'), Yaml::dump($configuration));
            }

            $this->prepareResources($resources);

            // Run command.
            $this->runCommand($command, false);

            // Assert expectations.
            foreach ($expectations as $expectation) {
                $content = file_get_contents($this->getSandboxFilepath($expectation['file']));
                $this->debugExpectations($content, $expectations);
                $this->assertDynamic($content, $expectation);
            }
        }

        /**
         * Test if configuration file exists.
         */
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
