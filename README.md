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
use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ClassNotation\NoBlankLinesAfterClassOpeningFixer;
use PhpCsFixer\Fixer\FunctionNotation\VoidReturnFixer;

return register_fixers([
    NoBlankLinesAfterClassOpeningFixer::class => false,
    ClassDefinitionFixer::class => false,
    VoidReturnFixer::class => true,
])
    ->withParallel()
     ->withSets([ SetList::DIGITAL_CREATIVE ])
    ->withPaths([
        __DIR__,
        __DIR__ . '/app',
        __DIR__ . '/database',
        __DIR__ . '/config',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ]);
```

- Run the `./vendor/bin/ecs check --fix`
