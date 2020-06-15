<?php

declare(strict_types=1);

namespace Odiseo\SyliusMercadoPagoPlugin\Payum;

final class MercadoPagoApi
{
    /** @var string */
    private $accessToken;

    /** @var bool */
    private $sandbox;

    public function __construct(
        string $accessToken,
        bool $sandbox
    ) {
        $this->accessToken = $accessToken;
        $this->sandbox = $sandbox;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return bool
     */
    public function isSandbox(): bool
    {
        return $this->sandbox;
    }
}
