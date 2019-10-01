<?php

declare(strict_types=1);

namespace Odiseo\SyliusMercadoPagoPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class OdiseoSyliusMercadoPagoPlugin extends Bundle
{
    use SyliusPluginTrait;
}
