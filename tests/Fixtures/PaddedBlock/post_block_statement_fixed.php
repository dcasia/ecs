<?php

declare(strict_types = 1);

final class FormRegistry
{
    public function register(array $formDefinitions, string $filename): void
    {
        $slugs = [];

        foreach ($formDefinitions as $formDefinition) {

            if (isset($slugs[ $formDefinition->slug ])) {

                throw new RuntimeException(sprintf(
                    'Duplicate form definition slug %s in %s and %s.',
                    $formDefinition->slug,
                    $slugs[ $formDefinition->slug ],
                    $filename,
                ));

            }

            $slugs[ $formDefinition->slug ] = $filename;

        }

        if ($slugs === []) {
            return;
        }

        $this->persist($slugs);

        if ($filename === '') {
            return;
        }

        if ($formDefinitions === []) {
            return;
        }
    }

    private function persist(array $slugs): void
    {
    }
}
