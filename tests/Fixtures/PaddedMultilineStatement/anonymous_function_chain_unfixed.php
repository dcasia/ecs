<?php

declare(strict_types = 1);

LanguageSwitch::configureUsing(function (LanguageSwitch $switch): void {
    $switch
        ->locales([ 'en', 'zh' ])
        ->nativeLabel()
        ->userPreferredLocale(fn () => auth()->user()?->locale);
});
