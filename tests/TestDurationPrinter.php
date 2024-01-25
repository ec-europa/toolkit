<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests;

use PHPUnit\Framework\Test;
use PHPUnit\TextUI\DefaultResultPrinter;
use PHPUnit\TextUI\ResultPrinter;

/**
 * Custom PHPUnit printer.
 */
class TestDurationPrinter extends DefaultResultPrinter implements ResultPrinter
{

    /**
     * {@inheritdoc}
     */
    public function endTest(Test $test, float $time): void
    {
        printf("Test '%s' ended and took %s seconds.\n", $test->getName(), $time);
    }

}
