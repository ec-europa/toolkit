<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit;

use Symfony\Component\Process\Process;

/**
 * Toolkit mock class.
 */
final class Mock
{

    /**
     * Downloads the mock from the repo.
     *
     * @throws \Exception
     *   If missing env var TOOLKIT_MOCK_REPO.
     */
    public static function download(): bool
    {
        $mockDir = getenv('TOOLKIT_MOCK_DIR') ?: 'mock';
        if (file_exists($mockDir)) {
            return true;
        }
        if (empty($repo = getenv('TOOLKIT_MOCK_REPO'))) {
            throw new \Exception('Missing env var TOOLKIT_MOCK_REPO.');
        }
        $branch = getenv('TOOLKIT_MOCK_BRANCH') ?: 'mock';
        $command = "git clone --depth 1 --branch $branch $repo $mockDir";
        $process = Process::fromShellCommandline($command);
        $process->run();
        if ($process->getExitCode()) {
            throw new \Exception($process->getErrorOutput());
        }
        return file_exists($mockDir);
    }

    /**
     * Returns the content of the endpoint from the mock.
     *
     * @param string $endpoint
     *   The endpoint to return the content from.
     *
     * @throws \Exception
     *   If the mock or endpoint file is not found.
     */
    public static function getEndpointContent(string $endpoint)
    {
        $mockDir = getenv('TOOLKIT_MOCK_DIR') ?: 'mock';
        if (!file_exists($mockDir)) {
            throw new \Exception("Mock not found at '$mockDir'.");
        }
        $endpointFile = "$mockDir/mock/$endpoint.json";
        if (!file_exists($endpointFile)) {
            throw new \Exception("No file found for endpoint '$endpoint'.");
        }
        return file_get_contents($endpointFile);
    }

}
