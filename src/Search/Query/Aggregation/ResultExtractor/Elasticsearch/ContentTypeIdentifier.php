<?php

declare(strict_types=1);

namespace App\Search\Query\Aggregation\ResultExtractor\Elasticsearch;

use App\Search\Query\Aggregation\ContentTypeIdentifier as ContentTypeIdentifierAggregation;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\TermAggregationResult;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\TermAggregationResultEntry;
use Ibexa\Contracts\Elasticsearch\Query\AggregationResultExtractor;
use Ibexa\Contracts\Elasticsearch\Query\LanguageFilter;

class ContentTypeIdentifier implements AggregationResultExtractor
{
    private ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    public function supports(Aggregation $aggregation, LanguageFilter $languageFilter): bool
    {
        return $aggregation instanceof ContentTypeIdentifierAggregation;
    }

    /** @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException */
    public function extract(Aggregation $aggregation, LanguageFilter $languageFilter, array $data): AggregationResult
    {
        $entries = [];
        foreach ($data['buckets'] as $bucket) {
            $entries[] = new TermAggregationResultEntry(
                $this->contentTypeService->loadContentTypeByIdentifier($bucket['key']),
                $bucket['doc_count']
            );
        }

        return new TermAggregationResult($aggregation->getName(), $entries);
    }
}
