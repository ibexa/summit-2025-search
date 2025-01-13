<?php

declare(strict_types=1);

namespace App\Search\Query\SortClause\Solr;

use App\Search\Index\ContentTypeIdentifierTrait;
use App\Search\Query\SortClause\ContentTypeIdentifier as ContentTypeIdentifierSortClause;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Solr\Query\SortClauseVisitor;

class ContentTypeIdentifier extends SortClauseVisitor
{
    use ContentTypeIdentifierTrait;

    public function canVisit(SortClause $sortClause): bool
    {
        return $sortClause instanceof ContentTypeIdentifierSortClause;
    }

    public function visit(SortClause $sortClause): string
    {
        return self::FIELD_IDENTIFIER . ' ' . $this->getDirection($sortClause);
    }
}
