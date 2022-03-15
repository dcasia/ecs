# Installation


- Add this repository to composer.json

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/dcasia/ecs.git"
    }
]
```

- Then install it via `composer require digital-creative/ecs`

- Create a file named `ecs.php` in the root directory of your project with the following content:

```php
<?php

declare(strict_types = 1);

use DigitalCreative\ECS\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;

return static function (ContainerConfigurator $configurator): void {

    $parameters = $configurator->parameters();
    $parameters->set(Option::PARALLEL, true);
    $parameters->set(Option::PATHS, [
        __DIR__ . '/app',
        __DIR__ . '/database',
        __DIR__ . '/config',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ]);

    $configurator->import(SetList::PHP_CS_FIXER);
    $configurator->import(SetList::CUSTOM);

    /**
     * Ignore specific fixers imported via above set lists
     */
    $parameters->set(Option::SKIP, [
        // IgnoreFixer::class
    ]);

    /**
     * Or Manually include new fixers
     */
    $options = [
        // ManuallyConfigureFixer::class => [ 'space' => 'single' ],
        // AddFixerWithDefaultConfiguration::class => true,
        // IgnoreFixer::class => false,
    ];

    register_fixers($configurator, $options);

};
```

- Run the `./vendor/bin/ecs check --fix`
