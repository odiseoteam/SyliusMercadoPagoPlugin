<?php

declare(strict_types=1);

namespace Odiseo\SyliusMercadoPagoPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        return new TreeBuilder('odiseo_sylius_mercado_pago_plugin');
    }
}
