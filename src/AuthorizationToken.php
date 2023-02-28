<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit;

/**
 * Authorization Token for QA api.
 */
class AuthorizationToken implements AuthorizationInterface
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getAuthorizationHeader(): string
    {
        return 'qa-user-auth-token: ' . $this->value;
    }
}
