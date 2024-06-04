<?php

use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\Configuration\ECSConfigBuilder;

function register_fixers(array $fixers): ECSConfigBuilder
{
    $config = ECSConfig::configure();

    foreach ($fixers as $fixer => $options) {

        if (is_bool($options)) {

            if ($options) {
                $config->withRules([ $fixer ]);
            }

        }

        if (is_array($options)) {
            $config->withConfiguredRule($fixer, $options);
        }

    }

    return $config;
}
