<?php

declare(strict_types=1);

namespace App\Search\Wrapper;

use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Search\Handler;

/**
 * Search engines wrapper balancing on FullText usage.
 *
 * This is a search handler wrapping several search engines.
 * If the FullText criterion is used, it performs the query on the first search engine capable of advanced fulltext search.
 * If the FullText criterion isn't used, it performs the query on the last search engine of its list.
 */
class FullTextHandler extends AbstractHandler
{
    public function getRegularSearchEngine(): Handler
    {
        $searchEngines = $this->getSearchEngines();
        return end($searchEngines);
    }

    public function getFullTextSearchEngine(): Handler
    {
        return $this->getCapableSearchEngine(SearchService::CAPABILITY_ADVANCED_FULLTEXT)
            ?? $this->getRegularSearchEngine();
    }

    public function getCapableSearchEngine(int $capabilityFlag): ?Handler
    {
        foreach ($this->getSearchEngines() as $searchEngine) {
            if ($searchEngine->supports($capabilityFlag)) {
                return $searchEngine;
            }
        }

        return null;
    }

    public function supports(int $capabilityFlag): bool
    {
        return null !== $this->getCapableSearchEngine($capabilityFlag);
    }

    public function isUsingFullText(Criterion $criteria)
    {
         switch ((new \ReflectionClass($criteria))->getShortName()) {
             case 'FullText': return true;
             case 'LogicalNot': return $this->isUsingFullText($criteria->criteria[0]);
             case 'LogicalAnd':
             case 'LogicalOr':
                 return $this->isUsingFullText($criteria->criteria[0]) || $this->isUsingFullText($criteria->criteria[1]);
             default: return false;
        }
    }

    public function getContentSearchEngine(Query $query, array $languageFilter = []): Handler
    {
        return $this->isUsingFullText($query->query) || $this->isUsingFullText($query->filter) ?
            $this->getFullTextSearchEngine() : $this->getRegularSearchEngine();
    }

    public function getSingleSearchEngine(Criterion $filter, array $languageFilter = []): Handler
    {
        return $this->isUsingFullText($filter) ?
            $this->getFullTextSearchEngine() : $this->getRegularSearchEngine();
    }

    public function getLocationSearchEngine(LocationQuery $query, array $languageFilter = []): Handler
    {
        return $this->isUsingFullText($query->query) || $this->isUsingFullText($query->filter) ?
            $this->getFullTextSearchEngine() : $this->getRegularSearchEngine();
    }

    public function getSuggestionSearchEngine($prefix, $fieldPaths = [], $limit = 10, Criterion $filter = null): Handler
    {
        return $this->isUsingFullText($filter) ?
            $this->getFullTextSearchEngine() : $this->getRegularSearchEngine();
    }
}
