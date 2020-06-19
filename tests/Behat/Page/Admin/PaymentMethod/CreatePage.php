<?php

declare(strict_types=1);

namespace Tests\Odiseo\SyliusMercadoPagoPlugin\Behat\Page\Admin\PaymentMethod;

use Sylius\Behat\Page\Admin\PaymentMethod\CreatePage as BaseCreatePage;

class CreatePage extends BaseCreatePage implements CreatePageInterface
{
    public function setMercadoPagoAccessToken(string $accessToken): void
    {
        $this->getDocument()->fillField('Access Token', $accessToken);
    }
}
