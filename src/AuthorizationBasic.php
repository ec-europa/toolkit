<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit;

/**
 * Authorization Basic for QA api.
 */
class AuthorizationBasic implements AuthorizationInterface
{
    private string $value;

    /**
     * Constructs a new Authorization with Basic auth.
     *
     * @param string $value
     *   The basic auth value.
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationHeader(): string
    {
        return 'Authorization: Basic ' . $this->value;
    }

}
