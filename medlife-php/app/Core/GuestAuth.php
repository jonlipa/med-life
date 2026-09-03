<?php

declare(strict_types=1);

namespace App\Core;

final class GuestAuth
{
    public function user(): ?array
    {
        return null;
    }

    public function role(): ?string
    {
        return null;
    }

    public function check(): bool
    {
        return false;
    }
}
