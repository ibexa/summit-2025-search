<?php

declare(strict_types=1);

namespace App\Search\Wrapper;

use Ibexa\Bundle\Core\ApiLoader\SearchEngineFactory;
use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Core\Persistence\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Search\Handler;
use Ibexa\Contracts\Core\Search\VersatileHandler;

/**
 * Search engines wrapper abstract.
 *
 * Base logic for a search handler having a list of search engines.
 * It performs indexing on all its search engines.
 * It performs search query on one search engine,
 * and the child class has to implement how this search engine is selected.
 */
abstract class AbstractHandler implements VersatileHandler
{
    /*------*/
    /* Wrap */

    /** @var array<int, string> */
    private array $searchEngineAliases;

    /** @var null|array<int, Handler> */
    private ?array $searchEngines = null;

    protected SearchEngineFactory $searchEngineFactory;

    /** @param string[] $searchEngineAliases */
    public function __construct(
        SearchEngineFactory $searchEngineFactory,
        array $searchEngineAliases = ['legacy']
    ) {
        $this->searchEngineAliases = $searchEngineAliases;
        $this->searchEngineFactory = $searchEngineFactory;
    }

    /** @return array<int, string> */
    public function getSearchEngineAliases(): array
    {
        return $this->searchEngineAliases;
    }

    /** @return array<int, Handler> */
    public function getSearchEngines(): array
    {
        if (null === $this->searchEngines) {
            $allSearchEngines = $this->searchEngineFactory->getSearchEngines();
            $wrappedSearchEngines = [];
            foreach ($this->getSearchEngineAliases() as $searchEngineAlias) {
                if (array_key_exists($searchEngineAlias, $allSearchEngines)) {
                    $wrappedSearchEngines[] = $allSearchEngines[$searchEngineAlias];
                } else {
                    throw new \InvalidArgumentException("Invalid search engine alias '{$searchEngineAlias}'.");
                }
            }
            $this->searchEngines = $wrappedSearchEngines;
        }

        return $this->searchEngines;
    }

    /*--------*/
    /* Search */

    // abstract public function supports(int $capabilityFlag): bool;

    abstract public function getContentSearchEngine(Query $query, array $languageFilter = []): Handler;

    public function findContent(Query $query, array $languageFilter = [])
    {
        return $this->getContentSearchEngine($query, $languageFilter)->findContent($query, $languageFilter);
    }

    abstract public function getSingleSearchEngine(Criterion $filter, array $languageFilter = []): Handler;

    public function findSingle(Criterion $filter, array $languageFilter = [])
    {
        return $this->getSingleSearchEngine($filter, $languageFilter)->findSingle($filter, $languageFilter);
    }

    abstract public function getLocationSearchEngine(LocationQuery $query, array $languageFilter = []): Handler;

    public function findLocations(LocationQuery $query, array $languageFilter = [])
    {
        return $this->getLocationSearchEngine($query, $languageFilter)->findLocations($query, $languageFilter);
    }

    abstract public function getSuggestionSearchEngine($prefix, $fieldPaths = [], $limit = 10, Criterion $filter = null): Handler;

    public function suggest($prefix, $fieldPaths = [], $limit = 10, Criterion $filter = null)
    {
        return $this->getSuggestionSearchEngine($prefix, $fieldPaths, $limit, $filter)->suggest($prefix, $fieldPaths, $limit, $filter);
    }

    /*-------*/
    /* Index */

    public function deleteTranslation(int $contentId, string $languageCode): void
    {
        foreach ($this->getSearchEngines() as $searchEngine) {
            $searchEngine->deleteTranslation($contentId, $languageCode);
        }
    }

    public function indexContent(Content $content)
    {
        foreach ($this->getSearchEngines() as $searchEngine) {
            $searchEngine->indexContent($content);
        }
    }

    public function deleteContent($contentId, $versionId = null)
    {
        foreach ($this->getSearchEngines() as $searchEngine) {
            $searchEngine->deleteContent($contentId, $versionId);
        }
    }

    public function indexLocation(Location $location)
    {
        foreach ($this->getSearchEngines() as $searchEngine) {
            $searchEngine->indexLocation($location);
        }
    }

    public function deleteLocation($locationId, $contentId)
    {
        foreach ($this->getSearchEngines() as $searchEngine) {
            $searchEngine->deleteLocation($locationId, $contentId);
        }
    }

    public function purgeIndex()
    {
        foreach ($this->getSearchEngines() as $searchEngine) {
            $searchEngine->purgeIndex();
        }
    }
}
