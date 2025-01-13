<?php

declare(strict_types=1);

namespace App\Search\Query\Aggregation\Visitor\Solr;

use App\Search\Index\ContentTypeIdentifierTrait;
use App\Search\Query\Aggregation\ContentTypeIdentifier as ContentTypeIdentifierAggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractTermAggregation;
use Ibexa\Contracts\Solr\Query\AggregationVisitor;
use Ibexa\Solr\Query\Common\AggregationVisitor\AbstractTermAggregationVisitor;

class ContentTypeIdentifier /* * /extends AbstractTermAggregationVisitor/* */ implements AggregationVisitor
{
    use ContentTypeIdentifierTrait;

    public function canVisit(Aggregation $aggregation, array $languageFilter): bool
    {
        return $aggregation instanceof ContentTypeIdentifierAggregation;
    }

    /** @param Aggregation\AbstractTermAggregation $aggregation */
    public function visit(AggregationVisitor $dispatcherVisitor, Aggregation $aggregation, array $languageFilter): array
    {
        return [
            'type' => 'terms',
            'field' => self::FIELD_IDENTIFIER,
            'limit' => $aggregation->getLimit(),
            'mincount' => $aggregation->getMinCount(),
        ];
    }
    /* */

    /* * /
    protected function getTargetField(AbstractTermAggregation $aggregation): string
    {
        return self::FIELD_IDENTIFIER;
    }
    /* */
}
