<?php

declare(strict_types = 1);

final class VehicleRepository
{
    public function exists(User $user, Vehicle $vehicle): bool
    {
        return $this->applyVisibleTo($this->query(), $user)
            ->whereKey($vehicle->getKey())
            ->exists();
    }
}
