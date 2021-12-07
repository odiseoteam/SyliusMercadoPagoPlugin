<?php

declare(strict_types=1);

namespace Odiseo\SyliusMercadoPagoPlugin\Payum;

final class MercadoPagoApi
{
    private string $accessToken;
    private bool $sandbox;

    public function __construct(
        string $accessToken,
        bool $sandbox
    ) {
        $this->accessToken = $accessToken;
        $this->sandbox = $sandbox;
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
