<?php

declare(strict_types=1);

namespace App\Search\Query\Criterion\Visitor\Solr;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

class ContentTypeIdentifier extends CriterionVisitor
{
    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof Criterion\ContentTypeIdentifier;
    }

    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        return '(' .
            implode(
                ' OR ',
                array_map(
                    static function ($value) {
                        return 'content_type_identifier_id:"' . $value . '"';
                    },
                    $criterion->value
                )
            ) .
            ')';
    }
}
