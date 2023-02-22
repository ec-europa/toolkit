<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit;

/**
 * Authorization Token for QA api.
 */
class AuthorizationBasic implements AuthorizationInterface
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getHeader(): string
    {
        return 'Authorization: Basic ' . $this->value;
    }

}
