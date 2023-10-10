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
     * The default mock tag to use to download and local directory.
     *
     * @var string
     */
    private static string $defaultTag = '0.0.2';

    /**
     * The directory to download the mock to.
     *
     * @var string
     */
    private static string $directory = '.toolkit-mock';

    /**
     * Downloads the mock from the repo.
     *
     * @throws \Exception
     *   If missing env var TOOLKIT_MOCK_REPO.
     */
    public static function download(): bool
    {
        if (!Toolkit::isCiCd()) {
            return false;
        }
        $tag = self::tag();
        $mockDir = self::$directory . '/' . $tag;
        if (file_exists($mockDir)) {
            return true;
        }
        $repo = self::repo();
        $command = "git clone --depth 1 --branch $tag $repo $mockDir";
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
        if (!Toolkit::isCiCd()) {
            return false;
        }
        $tag = self::tag();
        $mockDir = self::$directory . '/' . $tag;
        if (!file_exists($mockDir)) {
            throw new \Exception("Mock not found at '$mockDir'.");
        }
        $endpointFile = "$mockDir/$endpoint.json";
        if (!file_exists($endpointFile)) {
            throw new \Exception("No file found for endpoint '$endpoint'.");
        }
        return file_get_contents($endpointFile);
    }

    /**
     * Returns the repository url.
     *
     * @throws \Exception
     *   If missing env var TOOLKIT_MOCK_REPO.
     */
    public static function repo(): string
    {
        if (empty($repo = getenv('TOOLKIT_MOCK_REPO'))) {
            throw new \Exception('Missing env var TOOLKIT_MOCK_REPO.');
        }
        return (string) $repo;
    }

    /**
     * Returns the tag to use.
     */
    public static function tag(): string
    {
        if (!empty($tag = getenv('TOOLKIT_MOCK_TAG'))) {
            return (string) $tag;
        }
        return self::$defaultTag;
    }

}
