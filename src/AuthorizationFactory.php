<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit;

/**
 * Authorization factory for QA api.
 */
class AuthorizationFactory
{

    /**
     * Create a new Authorization.
     *
     * @param string $type
     *   The type to create.
     * @param string $value
     *   The value to use.
     */
    public static function create(string $type, string $value): AuthorizationInterface
    {
        switch ($type) {
            case 'basic':
                return new AuthorizationBasic($value);

            default:
                return new AuthorizationToken($value);
        }
    }

}
