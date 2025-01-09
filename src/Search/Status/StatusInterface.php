<?php

declare(strict_types=1);

namespace App\Search\Status;

interface StatusInterface
{
    public function isAlive(): bool;
}
