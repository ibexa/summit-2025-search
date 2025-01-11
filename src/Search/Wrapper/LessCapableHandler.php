<?php

declare(strict_types=1);

namespace App\Search\Wrapper;

use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Search\Handler;

/**
 * Search engines wrapper balancing on capabilities usage.
 *
 * This is a search handler wrapping several search engines ordered from more capable to less capable.
 * It studies the query to know the needed capabilities,
 * then selects the less capable search engine covering those needed capabilities.
 */
class LessCapableHandler extends AbstractHandler
{
    public function supports(int $capabilityFlag): bool
    {
        try {
            return null !== $this->getLessCapableSearchEngine([$capabilityFlag]);
        } catch (\Throwable $exception) {
            return false;
        }
    }

    /**
     * @param array<int, int> $neededCapabilities List of needed search service capabilities
     *     - {@see SearchService::CAPABILITY_SCORING}
     *     - {@see SearchService::CAPABILITY_ADVANCED_FULLTEXT}
     *     - {@see SearchService::CAPABILITY_AGGREGATIONS}
     */
    public function getLessCapableSearchEngine(array $neededCapabilities): /*?*/Handler
    {
        foreach (array_reverse($this->getSearchEngines()) as $searchEngine) {
            foreach ($neededCapabilities as $capability) {
                if (!$searchEngine->supports($capability)) {
                    continue 2;
                }
            }

            return $searchEngine;
        }

        //TODO: Return a default search engine? Return the most capable search engine? Or throw an error?
        //      For example, legacy support FullText criterion even if it doesn't support CAPABILITY_ADVANCED_FULLTEXT
        //return $this->getSearchEngines()[0];
        //return null;
        $searchServiceClass = new \ReflectionClass(SearchService::class);
        $searchServiceCapabilityConstantNames = array_flip($searchServiceClass->getConstants());
        $unsupportedCapabilities = [];
        $unknownCapabilities = [];
        foreach ($neededCapabilities as $capability) {
            if (array_key_exists($capability, $searchServiceCapabilityConstantNames)) {
                $unsupportedCapabilities[] = 'SearchService::' . $searchServiceCapabilityConstantNames[$capability];
            } else {
                $unknownCapabilities[] = $capability;
            }
        }
        if (!empty($unknownCapabilities)) {
            throw new \InvalidArgumentException('Unknown search engine capapility(ies): ' . implode(', ', $unknownCapabilities));
        } else {
            throw new \InvalidArgumentException('No search engine found to support the following capapility(ies): ' . implode(', ', $unsupportedCapabilities));
        }
    }

    public function getQueryNeededCapabilities(Query $query): array
    {
        $capabilities = [];

        $capabilities = array_merge($capabilities, $this->getCriterionNeededCapabilities($query->query));
        $capabilities = array_merge($capabilities, $this->getCriterionNeededCapabilities($query->filter));

        foreach ($query->sortClauses as $sortClause) {
            if ($sortClause instanceof Query\SortClause\Score) {
                $capabilities[] = SearchService::CAPABILITY_SCORING;
            }
        }

        if (!empty($query->aggregations)) {
            $capabilities[] = SearchService::CAPABILITY_AGGREGATIONS;
        }

        return array_unique($capabilities);
    }

    public function getCriterionNeededCapabilities(?Criterion $criterion = null): array
    {
        if (null === $criterion) {
            return [];
        }

        $capabilities = [];
        switch (get_class($criterion)) {
            case Criterion\FullText::class:
                $capabilities[] = SearchService::CAPABILITY_ADVANCED_FULLTEXT;
                break;
            case Criterion\LogicalNot::class:
                /** @var Criterion\LogicalNot $criterion */
                $capabilities = array_merge($capabilities, $this->getCriterionNeededCapabilities($criterion->criteria[0]));
                break;
            case Criterion\LogicalAnd::class:
            case Criterion\LogicalOr::class:
                /** @var Criterion\LogicalOperator $criterion */
                $capabilities = array_merge($capabilities, $this->getCriterionNeededCapabilities($criterion->criteria[0]));
                $capabilities = array_merge($capabilities, $this->getCriterionNeededCapabilities($criterion->criteria[1]));
                break;
        }

        return array_unique($capabilities);
    }

    public function getContentSearchEngine(Query $query, array $languageFilter = []): Handler
    {
        return $this->getLessCapableSearchEngine($this->getQueryNeededCapabilities($query));
    }

    public function getSingleSearchEngine(Criterion $filter, array $languageFilter = []): Handler
    {
        return $this->getLessCapableSearchEngine($this->getCriterionNeededCapabilities($filter));
    }

    public function getLocationSearchEngine(LocationQuery $query, array $languageFilter = []): Handler
    {
        return $this->getLessCapableSearchEngine($this->getQueryNeededCapabilities($query));
    }

    public function getSuggestionSearchEngine($prefix, $fieldPaths = [], $limit = 10, Criterion $filter = null): Handler
    {
        return $this->getLessCapableSearchEngine($this->getCriterionNeededCapabilities($filter));
    }
}
