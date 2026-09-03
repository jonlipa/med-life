<?php

declare(strict_types=1);

namespace App\Support;

final class Paginator
{
    public function __construct(
        private readonly int $currentPage,
        private readonly int $perPage,
        private readonly int $totalItems,
    ) {
    }

    public static function fromRequest(int $totalItems, int $perPage = 15): self
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));

        return new self($page, $perPage, $totalItems);
    }

    public function totalPages(): int
    {
        if ($this->totalItems <= 0) {
            return 1;
        }

        return (int) ceil($this->totalItems / $this->perPage);
    }

    public function hasNext(): bool
    {
        return $this->currentPage < $this->totalPages();
    }

    public function hasPrev(): bool
    {
        return $this->currentPage > 1;
    }

    public function offset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    public function limit(): int
    {
        return $this->perPage;
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function totalItems(): int
    {
        return $this->totalItems;
    }
}
