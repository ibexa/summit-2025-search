<?php

declare(strict_types=1);

namespace App\Search\Query\Aggregation\ResultExtractor\Solr;

use App\Search\Query\Aggregation\ContentTypeIdentifier as ContentTypeIdentifierAggregation;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\TermAggregationResult;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\TermAggregationResultEntry;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor;
use stdClass;

class ContentTypeIdentifier implements AggregationResultExtractor
{
    private ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    public function canVisit(Aggregation $aggregation, array $languageFilter): bool
    {
        return $aggregation instanceof ContentTypeIdentifierAggregation;
    }

    /** @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException */
    public function extract(Aggregation $aggregation, array $languageFilter, stdClass $data): AggregationResult
    {
        $entries = [];
        foreach ($data->buckets as $bucket) {
            $entries[] = new TermAggregationResultEntry(
                $this->contentTypeService->loadContentTypeByIdentifier($bucket->val),
                $bucket->count
            );
        }

        return new TermAggregationResult($aggregation->getName(), $entries);
    }
}
