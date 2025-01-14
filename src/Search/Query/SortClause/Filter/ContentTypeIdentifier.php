<?php

declare(strict_types=1);

namespace App\Search\Query\SortClause\Filter;

use App\Search\Query\SortClause\ContentTypeIdentifier as ContentTypeIdentifierSortClause;
use Ibexa\Contracts\Core\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use Ibexa\Contracts\Core\Repository\Values\Filter\FilteringSortClause;
use Ibexa\Contracts\Core\Repository\Values\Filter\SortClauseQueryBuilder;
use Ibexa\Core\Persistence\Legacy\Content\Type\Gateway as ContentTypeGateway;

class ContentTypeIdentifier implements SortClauseQueryBuilder
{
    public function accepts(FilteringSortClause $sortClause): bool
    {
        return $sortClause instanceof ContentTypeIdentifierSortClause;
    }

    public function buildQuery(FilteringQueryBuilder $queryBuilder, FilteringSortClause $sortClause): void
    {
        $queryBuilder
            ->joinOnce(
                'content',
                ContentTypeGateway::CONTENT_TYPE_TABLE,
                'content_type',
                'content.contentclass_id = content_type.id AND content_type.version = 0'
            )
            ->addSelect('content_type.identifier')
            ->addOrderBy('content_type.identifier', $sortClause->direction)
        ;
    }
}
