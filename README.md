<h1 align="center">
    <a href="https://odiseo.com.ar/" target="_blank" title="Odiseo">
        <img src="https://github.com/odiseoteam/SyliusMercadoPagoPlugin/blob/master/logo_odiseo.png" alt="Odiseo" width="300px" />
    </a>
    <br />
    Odiseo Sylius Mercado Pago Plugin
    <br />
    <a href="https://packagist.org/packages/odiseoteam/sylius-mercado-pago-plugin" title="License" target="_blank">
        <img src="https://img.shields.io/packagist/l/odiseoteam/sylius-mercado-pago-plugin.svg" />
    </a>
    <a href="https://packagist.org/packages/odiseoteam/sylius-mercado-pago-plugin" title="Version" target="_blank">
        <img src="https://img.shields.io/packagist/v/odiseoteam/sylius-mercado-pago-plugin.svg" />
    </a>
    <a href="http://travis-ci.org/odiseoteam/SyliusMercadoPagoPlugin" title="Build status" target="_blank">
        <img src="https://img.shields.io/travis/odiseoteam/SyliusMercadoPagoPlugin/master.svg" />
    </a>
    <a href="https://scrutinizer-ci.com/g/odiseoteam/SyliusMercadoPagoPlugin/" title="Scrutinizer" target="_blank">
        <img src="https://img.shields.io/scrutinizer/g/odiseoteam/SyliusMercadoPagoPlugin.svg" />
    </a>
    <a href="https://packagist.org/packages/odiseoteam/sylius-mercado-pago-plugin" title="Total Downloads" target="_blank">
        <img src="https://poser.pugx.org/odiseoteam/sylius-mercado-pago-plugin/downloads" />
    </a>
</h1>

## Description

This plugin add Mercado Pago payment method to the Sylius project.
[Mercado Pago Developers](https://www.mercadopago.com.ar/developers/es/guides).

<img src="https://github.com/odiseoteam/SyliusMercadoPagoPlugin/blob/master/screenshot_1.png" alt="Mercado Pago payment">

## Demo

You can see this plugin in action in our Sylius Demo application.

- Frontend: [sylius-demo.odiseo.com.ar](https://sylius-demo.odiseo.com.ar). 
- Administration: [sylius-demo.odiseo.com.ar/admin](https://sylius-demo.odiseo.com.ar/admin) with `odiseo: odiseo` credentials.

## Installation

1. Run `composer require odiseoteam/sylius-mercado-pago-plugin`

2. Enable the plugin in bundles.php:

```php
<?php

return [
    // ...
    Odiseo\SyliusMercadoPagoPlugin\OdiseoSyliusMercadoPagoPlugin::class => ['all' => true],
];
```

3. Import the plugin configurations

```yml
imports:
    - { resource: "@OdiseoSyliusMercadoPagoPlugin/Resources/config/config.yaml" }
```

## Test the plugin

You can follow the instructions to test this plugins in the proper documentation page: [Test the plugin](doc/tests.md).

## Credits

This plugin is maintained by <a href="https://odiseo.io">Odiseo</a>. Want us to help you with this plugin or any Sylius project? Contact us on <a href="mailto:team@odiseo.com.ar">team@odiseo.com.ar</a>.
