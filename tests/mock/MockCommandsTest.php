<?php

/**
 * @file
 * Mock functions for EcEuropa\Toolkit\TaskRunner\Commands.
 */

declare(strict_types = 1);

namespace EcEuropa\Toolkit\TaskRunner\Commands;

/**
 * Override random_bytes function for test.
 */
function random_bytes() {
  return 'abc';
}
