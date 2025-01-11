<?php

declare(strict_types=1);

namespace App\Search\Wrapper;

use App\Search\Wrapper\AbstractHandler as SearchHandler;
use Doctrine\DBAL\Connection;
use Ibexa\Bundle\Core\ApiLoader\SearchEngineIndexerFactory;
use Ibexa\Contracts\Core\Persistence\Handler as PersistenceHandler;
use Ibexa\Core\Search\Common\IncrementalIndexer;
use Psr\Log\LoggerInterface;

/**
 * Wrapped search engines indexer.
 */
class Indexer extends IncrementalIndexer
{
    protected SearchEngineIndexerFactory $searchEngineIndexerFactory;

    /** @var null|array<int, \Ibexa\Core\Search\Common\Indexer> */
    private ?array $searchEngineIndexers = null;

    public function __construct(
        LoggerInterface            $logger,
        PersistenceHandler         $persistenceHandler,
        Connection                 $connection,
        SearchHandler              $searchHandler,
        SearchEngineIndexerFactory $searchEngineIndexerFactory
    )
    {
        parent::__construct($logger, $persistenceHandler, $connection, $searchHandler);
        $this->searchEngineIndexerFactory = $searchEngineIndexerFactory;
    }

    /** @return array<int, \Ibexa\Core\Search\Common\Indexer> */
    public function getSearchEngineIndexers(): array
    {
        if (null === $this->searchEngineIndexers) {
            $allSearchEngineIndexers = $this->searchEngineIndexerFactory->getSearchEngineIndexers();
            $wrappedSearchEngineIndexers = [];
            /** @var SearchHandler $searchHandler */
            $searchHandler = $this->searchHandler;
            foreach ($searchHandler->getSearchEngineAliases() as $searchEngineAlias) {
                if (array_key_exists($searchEngineAlias, $allSearchEngineIndexers)) {
                    $wrappedSearchEngineIndexers[] = $allSearchEngineIndexers[$searchEngineAlias];
                } else {
                    $this->logger->error("Invalid search engine alias '{$searchEngineAlias}'.");
                }
            }
            $this->searchEngineIndexers = $wrappedSearchEngineIndexers;
        }

        return $this->searchEngineIndexers;
    }

    public function getName(): string
    {
        $name = 'Wrapped ';
        $searchEngineIndexers = $this->getSearchEngineIndexers();
        $searchEngineIndexerCount = count($searchEngineIndexers);
        foreach ($searchEngineIndexers as $indexerIndex => $indexer) {
            switch ($indexerIndex) {
                case 0:
                    $name .= $indexer->getName();
                    break;
                case $searchEngineIndexerCount - 1:
                    $name .= ' & ' . $indexer->getName();
                    break;
                default:
                    $name .= ', ' . $indexer->getName();
            }
        }

        return $name;
    }

    public function purge(): void
    {
        $this->searchHandler->purgeIndex();
    }

    public function updateSearchIndex(array $contentIds, $commit): void
    {
        foreach ($this->getSearchEngineIndexers() as $searchEngineIndexer) {
            $searchEngineIndexer->updateSearchIndex($contentIds, $commit);
        }
    }
}
