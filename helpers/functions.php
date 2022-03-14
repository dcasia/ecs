<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

function register_fixers(ContainerConfigurator $configurator, array $options): void
{
    $services = $configurator->services();

    foreach ($options as $fixer => $options) {

        if ($options) {

            $fixer = $services->set($fixer);

            if (is_array($options)) {
                $fixer->call('configure', [ $options ]);
            }

        }

    }
}
