<?php

declare(strict_types=1);

namespace App\Search\Query\Aggregation\Visitor\Elasticsearch;

use App\Search\Index\ContentTypeIdentifierTrait;
use App\Search\Query\Aggregation\ContentTypeIdentifier as ContentTypeIdentifierAggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractTermAggregation;
use Ibexa\Contracts\Elasticsearch\Query\AggregationVisitor;
use Ibexa\Contracts\Elasticsearch\Query\LanguageFilter;
use Ibexa\Elasticsearch\ElasticSearch\QueryDSL\TermsAggregation;
use Ibexa\Elasticsearch\Query\AggregationVisitor\AbstractTermAggregationVisitor;

class ContentTypeIdentifier /* * /extends AbstractTermAggregationVisitor/* */ implements AggregationVisitor
{
    use ContentTypeIdentifierTrait;

    public function supports(Aggregation $aggregation, LanguageFilter $languageFilter): bool
    {
        return $aggregation instanceof ContentTypeIdentifierAggregation;
    }

    /** @param Aggregation\AbstractTermAggregation $aggregation */
    public function visit(AggregationVisitor $dispatcher, Aggregation $aggregation, LanguageFilter $languageFilter): array
    {
        $termAggregation = new TermsAggregation(self::FIELD_IDENTIFIER);
        $termAggregation->withSize($aggregation->getLimit());
        $termAggregation->withMinDocCount($aggregation->getMinCount());

        return $termAggregation->toArray();
    }
    /* */

    /* * /
    protected function getTargetField(AbstractTermAggregation $aggregation): string
    {
        return self::FIELD_IDENTIFIER;
    }
    /* */
}
