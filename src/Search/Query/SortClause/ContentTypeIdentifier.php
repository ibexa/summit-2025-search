<?php

declare(strict_types=1);

namespace App\Search\Query\SortClause;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;

class ContentTypeIdentifier extends SortClause
{
    public function __construct(string $sortDirection = Query::SORT_ASC)
    {
        parent::__construct('TODO: whatever', $sortDirection);
    }
}
