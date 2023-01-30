<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests;

use PHPUnit\TextUI\DefaultResultPrinter;
use PHPUnit\TextUI\ResultPrinter;

class TestDurationPrinter extends DefaultResultPrinter implements ResultPrinter
{

    public function endTest(\PHPUnit\Framework\Test $test, float $time): void
    {
        printf("Test '%s' ended and took %s seconds.\n", $test->getName(), $time);
    }

}
