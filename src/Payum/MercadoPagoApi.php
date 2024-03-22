<?php

declare(strict_types=1);

namespace Odiseo\SyliusMercadoPagoPlugin\Payum;

final class MercadoPagoApi
{
    public function __construct(
        private string $accessToken,
        private bool $sandbox,
    ) {
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }
}
