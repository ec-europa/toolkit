<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit;

/**
 * Interface for Authorization classes.
 */
interface AuthorizationInterface
{

    /**
     * Returns the Authorization string for the header.
     */
    public function getAuthorizationHeader(): string;

}
