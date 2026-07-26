<?php

declare(strict_types = 1);

final class LinkedBlocks
{
    public function run(bool $condition): void
    {
        if ($condition) {

            return;

        } else {

            return;

        }

        try {

            $this->execute();

        } catch (RuntimeException) {

            return;

        } finally {

            $this->cleanup();

        }

        do {

            $condition = false;

        } while ($condition);
    }
}
