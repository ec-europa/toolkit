<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Unit;

use EcEuropa\Toolkit\TaskRunner\Commands\DumpCommands;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Unit tests for Dump commands.
 */
#[Group('dump')]
class DumpCommandsTest extends TestCase
{

    /**
     * Test parsing valid latest.sh1 content.
     */
    public function testParseLatestShaDataValidContent(): void
    {
        $method = new ReflectionMethod(DumpCommands::class, 'parseLatestShaData');
        $method->setAccessible(true);

        $result = $method->invoke(
            new DumpCommands(),
            "5baa8031434fb4e95a0982d65ed196271328bbe8  dump.sql.gz\n",
            'mysql'
        );

        $this->assertSame('5baa8031434fb4e95a0982d65ed196271328bbe8', $result['sha1']);
        $this->assertSame('dump.sql.gz', $result['filename']);
    }

    /**
     * Test parsing invalid latest.sh1 content.
     */
    public function testParseLatestShaDataInvalidContent(): void
    {
        $method = new ReflectionMethod(DumpCommands::class, 'parseLatestShaData');
        $method->setAccessible(true);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Failed parsing checksum for service 'mysql'.");
        $method->invoke(new DumpCommands(), "\n", 'mysql');
    }

    /**
     * Test checksum verification succeeds using a real fixture SHA1.
     */
    public function testVerifyDownloadedFileShaRealFixtureMatch(): void
    {
        $command = $this->createDumpCommandsMock(false);

        $file = realpath(__DIR__ . '/../fixtures/samples/sample-dump.sql.gz');
        $this->assertNotFalse($file);

        $method = new ReflectionMethod(DumpCommands::class, 'verifyDownloadedFileSha');
        $method->setAccessible(true);
        $method->invoke($command, '829c3804401b0727f70f73d4415e162400cbe57b', $file, 'mysql');

        $this->assertFileExists($file);
    }

    /**
     * Test checksum verification succeeds when checksum matches.
     */
    public function testVerifyDownloadedFileShaMatch(): void
    {
        $command = $this->createDumpCommandsMock(false);

        $file = tempnam(sys_get_temp_dir(), 'dump-sha-ok-');
        $this->assertNotFalse($file);
        file_put_contents($file, 'sample dump content');

        $method = new ReflectionMethod(DumpCommands::class, 'verifyDownloadedFileSha');
        $method->setAccessible(true);
        $method->invoke($command, sha1_file($file), $file, 'mysql');

        $this->assertFileExists($file);
        @unlink($file);
    }

    /**
     * Test checksum verification fails when checksum does not match.
     */
    public function testVerifyDownloadedFileShaMismatch(): void
    {
        $command = $this->createDumpCommandsMock(false);

        $file = tempnam(sys_get_temp_dir(), 'dump-sha-ko-');
        $this->assertNotFalse($file);
        file_put_contents($file, 'sample dump content');

        $method = new ReflectionMethod(DumpCommands::class, 'verifyDownloadedFileSha');
        $method->setAccessible(true);

        try {
            $method->invoke($command, '0000000000000000000000000000000000000000', $file, 'mysql');
            $this->fail('Expected checksum mismatch exception was not thrown.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString("Checksum mismatch for service 'mysql'", $e->getMessage());
        }
        $this->assertFileDoesNotExist($file);
    }

    /**
     * Create a partial mock for DumpCommands with mocked simulation status.
     */
    private function createDumpCommandsMock(bool $isSimulating): DumpCommands
    {
        $command = $this->getMockBuilder(DumpCommands::class)
            ->onlyMethods(['isSimulating'])
            ->getMock();
        $command->method('isSimulating')->willReturn($isSimulating);
        return $command;
    }

}
