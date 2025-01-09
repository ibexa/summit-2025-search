<?php

declare(strict_types=1);

namespace App\Search\Status;

class Legacy implements StatusInterface
{
    public function isAlive(): bool
    {
        return true;
    }
}
