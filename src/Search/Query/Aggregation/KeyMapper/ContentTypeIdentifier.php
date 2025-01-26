<?php

declare(strict_types=1);

namespace App\Search\Query\Aggregation\KeyMapper;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
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

    /** @throws NotFoundException if the index contains a document of unknown content type; Consider reindex.*/
    public function map(Aggregation $aggregation, LanguageFilter|array $languageFilter, array $keys): array
    {
        $map = [];

        foreach ($keys as $key) {
            $map[$key] = $this->contentTypeService->loadContentTypeByIdentifier($key);
        }

        return $map;
    }
}
