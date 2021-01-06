## Installation

1. Run `composer require odiseoteam/sylius-mercado-pago-plugin --no-scripts`

2. Enable the plugin in bundles.php

```php
<?php
// config/bundles.php

return [
    // ...
    Odiseo\SyliusMercadoPagoPlugin\OdiseoSyliusMercadoPagoPlugin::class => ['all' => true],
];
```

3. Import the plugin configurations

```yml
# config/packages/_sylius.yaml
imports:
    ...

    - { resource: "@OdiseoSyliusMercadoPagoPlugin/Resources/config/config.yaml" }
```
