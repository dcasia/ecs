<?php

declare(strict_types = 1);

$lines = [];
$records = [];

if (blank($lines)) {
    return;
}

if (filled($records)) {
    return;
}

$result = blank(trim($value));
