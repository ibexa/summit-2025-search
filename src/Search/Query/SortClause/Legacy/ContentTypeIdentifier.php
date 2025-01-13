<?php

declare(strict_types=1);

namespace App\Search\Query\SortClause\Legacy;

use App\Search\Query\SortClause\ContentTypeIdentifier as ContentTypeIdentifierSortClause;
use Doctrine\DBAL\Query\QueryBuilder;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Core\Persistence\Legacy\Content\Type\Gateway as ContentTypeGateway;
use Ibexa\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler;

class ContentTypeIdentifier extends SortClauseHandler
{

    public function accept(SortClause $sortClause)
    {
        return $sortClause instanceof ContentTypeIdentifierSortClause;
    }

    public function applySelect(
        QueryBuilder $query,
        SortClause $sortClause,
        int $number
    ): array {
        $query
            ->addSelect(
                sprintf(
                    'ct.identifier AS %s',
                    $column = $this->getSortColumnName($number)
                )
            );

        return [$column];
    }

    public function applyJoin(
        QueryBuilder $query,
        SortClause $sortClause,
        int $number,
        array $languageSettings
    ): void {
        $query->innerJoin(
            'c',
            ContentTypeGateway::CONTENT_TYPE_TABLE,
            'ct',
            'c.contentclass_id = ct.id'
        );
    }
}
