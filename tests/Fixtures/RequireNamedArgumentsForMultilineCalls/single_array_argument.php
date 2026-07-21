<?php

declare(strict_types = 1);

return register_fixers([
    BlankLineAfterNamespaceFixer::class => true,
    BlankLinesBeforeNamespaceFixer::class => true,
    CleanNamespaceFixer::class => true,
    NoLeadingNamespaceWhitespaceFixer::class => true,
]);
