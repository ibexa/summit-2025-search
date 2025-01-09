<?php

declare(strict_types=1);

namespace App\Search\Wrapper;

use App\Search\Status\StatusInterface;
use Ibexa\Bundle\Core\ApiLoader\SearchEngineFactory;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Search\Handler;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Search handler with fallback.
 *
 * This is a search handler wrapping several search engines.
 * It performs search query on the first available search engine.
 * So if the favorite search engine is off, it fallbacks to the next.
 */
class FirstAliveHandler extends AbstractHandler
{
    /** @var array<int, StatusInterface> */
    private ServiceLocator $statusServiceLocator;

    /**
     * @param array<int, string> $searchEngineAliases
     * @param array<int, StatusInterface> $statusServices
     */
    public function __construct(
        SearchEngineFactory $searchEngineFactory,
        array               $searchEngineAliases = [],
        ServiceLocator      $statusServiceLocator = null
    )
    {
        parent::__construct($searchEngineFactory, $searchEngineAliases);
        if ($missingStatusServices = array_diff($searchEngineAliases, array_keys($statusServiceLocator->getProvidedServices()))) {
            throw new \InvalidArgumentException('Missing status service(s): ' . implode(', ', $missingStatusServices));
        }
        $this->statusServiceLocator = $statusServiceLocator;
    }

    public function getFirstAliveSearchEngine(): Handler
    {
        foreach ($this->getSearchEngineAliases() as $searchEngineAlias) {
            if ($this->statusServiceLocator->get($searchEngineAlias)->isAlive()) {
                return $this->searchEngineFactory->getSearchEngines()[$searchEngineAlias];
            }
        }
    }

    public function supports(int $capabilityFlag): bool
    {
        return $this->getFirstAliveSearchEngine()->supports($capabilityFlag);
    }

    public function getContentSearchEngine(Query $query, array $languageFilter = []): Handler
    {
        return $this->getFirstAliveSearchEngine();
    }

    public function getSingleSearchEngine(Criterion $filter, array $languageFilter = []): Handler
    {
        return $this->getFirstAliveSearchEngine();
    }

    public function getLocationSearchEngine(LocationQuery $query, array $languageFilter = []): Handler
    {
        return $this->getFirstAliveSearchEngine();
    }

    public function getSuggestionSearchEngine($prefix, $fieldPaths = [], $limit = 10, Criterion $filter = null): Handler
    {
        return $this->getFirstAliveSearchEngine();
    }
}
