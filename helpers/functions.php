<?php

use Symplify\EasyCodingStandard\Config\ECSConfig;

function register_fixers(ECSConfig $config, array $options): void
{
    foreach ($options as $fixer => $options) {

        if (is_bool($options)) {

            if ($options) {
                $config->rule($fixer);
            } else {
                $config->skip([ $fixer ]);
            }

        }

        if (is_array($options)) {
            $config->ruleWithConfiguration($fixer, $options);
        }

    }
}
