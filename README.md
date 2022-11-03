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
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {

    $config->parallel();
    $config->paths([
        __DIR__ . '/app',
        __DIR__ . '/database',
        __DIR__ . '/config',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ]);

    $config->import(SetList::PHP_CS_FIXER);
    $config->import(SetList::CUSTOM);

    /**
     * Ignore specific fixers imported via above set lists
     */
    $config->skip([
        // IgnoreFixer::class
        NoBlankLinesAfterClassOpeningFixer::class,
        ClassDefinitionFixer::class,
    ]);

    /**
     * Or Manually include new fixers
     */
    $options = [
        // ManuallyConfigureFixer::class => [ 'space' => 'single' ],
        // AddFixerWithDefaultConfiguration::class => true,
        // IgnoreFixer::class => false,
    ];

    register_fixers($config, $options);

};
```

- Run the `./vendor/bin/ecs check --fix`
