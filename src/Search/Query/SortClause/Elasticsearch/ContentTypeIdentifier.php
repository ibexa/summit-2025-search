<?php

declare(strict_types=1);

namespace App\Search\Query\SortClause\Elasticsearch;

use App\Search\Index\ContentTypeIdentifierTrait;
use App\Search\Query\SortClause\ContentTypeIdentifier as ContentTypeIdentifierSortClause;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Elasticsearch\Query\LanguageFilter;
use Ibexa\Contracts\Elasticsearch\Query\SortClauseVisitor;

class ContentTypeIdentifier implements SortClauseVisitor
{
    use ContentTypeIdentifierTrait;

    public function supports(SortClause $sortClause, LanguageFilter $languageFilter): bool
    {
        return $sortClause instanceof ContentTypeIdentifierSortClause;
    }

    public function visit(SortClauseVisitor $visitor, SortClause $sortClause, LanguageFilter $languageFilter): array
    {
        $order = $sortClause->direction === Query::SORT_ASC ? 'asc' : 'desc';

        return [
            self::FIELD_IDENTIFIER => [
                'order' => $order,
            ],
        ];
    }
}
