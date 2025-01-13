<?php

declare(strict_types=1);

namespace App\Search\Query\Aggregation\KeyMapper;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Elasticsearch\Query\LanguageFilter;
use Ibexa\Elasticsearch\Query\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper as ElasticsearchTermAggregationKeyMapper;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper as SolrTermAggregationKeyMapper;


class ContentTypeIdentifier implements ElasticsearchTermAggregationKeyMapper, SolrTermAggregationKeyMapper
{
    private ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    public function map(Aggregation $aggregation, LanguageFilter|array $languageFilter, array $keys): array
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->contentTypeService->loadContentTypeByIdentifier($key);
        }

        return $result;
    }
}
