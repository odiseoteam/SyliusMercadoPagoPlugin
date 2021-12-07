<?php

declare(strict_types=1);

namespace Odiseo\SyliusMercadoPagoPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

final class MercadoPagoGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('access_token', TextType::class, [
                'label' => 'odiseo_sylius_mercado_pago_plugin.form.gateway_configuration.mercado_pago.access_token',
                'constraints' => [
                    new NotBlank([
                        'groups' => 'sylius',
                    ]),
                ],
            ])
            ->add('sandbox', CheckboxType::class, [
                'label' => 'odiseo_sylius_mercado_pago_plugin.form.gateway_configuration.mercado_pago.sandbox',
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                $data = $event->getData();

                /**
                 * @psalm-suppress MixedArrayAssignment
                 */
                $data['payum.http_client'] = '@sylius.payum.http_client';
            })
        ;
    }
}
