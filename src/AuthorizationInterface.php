<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit;

interface AuthorizationInterface
{

    public function getAuthorizationHeader(): string;
}
