<?php

namespace App\Command;

use App\Search\Query as CustomQuery;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Contracts\Core\Repository\Values\Filter\Filter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SearchCommand extends Command
{
    protected static $defaultName = 'app:search';
    private SearchService $searchService;
    private ContentService $contentService;
    private LocationService $locationService;

    public function __construct(SearchService $searchService, ContentService $contentService, LocationService $locationService)
    {
        $this->searchService = $searchService;
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        parent::__construct();
    }

    public function configure()
    {
        $this->addArgument('text', InputArgument::IS_ARRAY, 'Searched text');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $text = implode(' ', $input->getArgument('text'));

        //$query = new Query(['query' => new Query\Criterion\FullText($text)]);
        //$query = new Query(['filter' => new Query\Criterion\ContentTypeIdentifier('landing_page')]);
        $query = new Query(['filter' => new Query\Criterion\ContentTypeIdentifier(['landing_page', 'folder'])]);
        /* * /
        //$query = new Query([
        $query = new LocationQuery([
            'filter' => new Query\Criterion\ContentTypeIdentifier(['landing_page', 'folder']),
            'sortClauses' => [
                new CustomQuery\SortClause\ContentTypeIdentifier(),
                new Query\SortClause\ContentName(),
            ],
            //'aggregations' => [new Query\Aggregation\ContentTypeTermAggregation('Content types')],
            'aggregations' => [new CustomQuery\Aggregation\ContentTypeIdentifier('Content types')],
            'limit' => 100,
        ]);
        /* */

        $searchResult = $this->searchService->findContentInfo($query);
        //$searchResult = $this->searchService->findContent($query);
        //$searchResult = $this->searchService->findLocations($query);

        $this->displaySearchResult($searchResult, $output);

        //return Command::SUCCESS;

        $output->writeln('<info>= Filter =</info>');
        $filter = new Filter(new Query\Criterion\ContentTypeIdentifier(['landing_page', 'folder']), [
            new CustomQuery\SortClause\ContentTypeIdentifier(Query::SORT_DESC),
            new Query\SortClause\ContentName(Query::SORT_DESC)
        ]);
        /** @var Content $content */
        foreach ($this->contentService->find($filter)->getIterator() as $content) {
            $output->writeln("{$content->getName()} ({$content->getContentType()->getIdentifier()})");
        }
        /** @var Location $content */
        foreach ($this->locationService->find($filter)->getIterator() as $content) {
            $output->writeln("{$content->getContentInfo()->getName()} ({$content->getContentInfo()->getContentType()->getIdentifier()})");
        }

        return Command::SUCCESS;
    }

    private function displaySearchResult(SearchResult $searchResult, OutputInterface $output) {
        $output->writeln('<info>= Search Result =</info>');
        foreach ($searchResult->searchHits as $searchHit) {
            $scorePercent = $searchResult->maxScore ?
                str_pad(round(100 * $searchHit->score / $searchResult->maxScore), 3, ' ', STR_PAD_LEFT) . '% '
                : '';
            switch(get_parent_class($searchHit->valueObject)) {
                case Location::class:
                    /** @var Location $location */
                    $location = $searchHit->valueObject;
                    $contentInfo = $location->getContentInfo();
                    break;
                case Content::class:
                    /** @var Content $content */
                    $content = $searchHit->valueObject;
                    $contentInfo = $content->getContentInfo();
                    break;
                default:
                    switch(get_class($searchHit->valueObject)) {
                        case ContentInfo::class:
                            /** @var ContentInfo $contentInfo */
                            $contentInfo = $searchHit->valueObject;
                            break;
                        default:
                            throw new \RuntimeException('Not handled $searchHit->valueObject class or parent class' .
                                get_class($searchHit->valueObject) . ' ← ' . get_parent_class($searchHit->valueObject));
                    }
            }
            $output->writeln("{$scorePercent}{$contentInfo->getName()} [{$contentInfo->getContentType()->getName()} ({$contentInfo->getContentType()->getIdentifier()})]");
        }
        if (!empty($searchResult->aggregations)) {
            /** @var AggregationResult\TermAggregationResult $aggregation */
            foreach ($searchResult->aggregations as $aggregation) {
                $output->writeln("<comment>-- {$aggregation->getName()} aggregation --</comment>");
                /** @var AggregationResult\TermAggregationResultEntry $entry */
                foreach ($aggregation->getEntries() as $entry) {
                    $output->writeln("{$entry->getKey()->getName()} ({$entry->getKey()->getIdentifier()}): {$entry->getCount()}");
                }
            }
        }
    }
}
