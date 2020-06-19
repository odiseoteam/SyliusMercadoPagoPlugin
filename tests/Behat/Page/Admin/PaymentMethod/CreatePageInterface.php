<?php

declare(strict_types=1);

namespace Tests\Odiseo\SyliusMercadoPagoPlugin\Behat\Page\Admin\PaymentMethod;

use Sylius\Behat\Page\Admin\PaymentMethod\CreatePageInterface as BaseCreatePageInterface;

interface CreatePageInterface extends BaseCreatePageInterface
{
    public function setMercadoPagoAccessToken(string $accessToken): void;
}
