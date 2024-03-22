<?php

declare(strict_types=1);

namespace Odiseo\SyliusMercadoPagoPlugin\Payum;

use Odiseo\SyliusMercadoPagoPlugin\Payum\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class MercadoPagoGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'mercado_pago',
            'payum.factory_title' => 'Mercado Pago',
            'payum.action.status' => new StatusAction(),
        ]);

        $config['payum.api'] = function (ArrayObject $config): MercadoPagoApi {
            /** @var string $accessToken */
            $accessToken = $config['access_token'];
            /** @var bool $sandbox */
            $sandbox = $config['sandbox'];

            return new MercadoPagoApi(
                $accessToken,
                $sandbox,
            );
        };
    }
}
