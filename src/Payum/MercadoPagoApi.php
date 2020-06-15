<?php

declare(strict_types=1);

namespace Odiseo\SyliusMercadoPagoPlugin\Payum;

final class MercadoPagoApi
{
    /** @var string */
    private $accessToken;

    public function __construct(string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }
}
