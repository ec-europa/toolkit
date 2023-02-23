<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit;

/**
 * Authorization factory for QA api.
 */
class AuthorizationFactory
{

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
