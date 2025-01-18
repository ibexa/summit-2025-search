<?php

namespace App\Command;

use App\Search\Query as CustomQuery;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentList;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationList;
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
        $query = new LocationQuery(['filter' => new Query\Criterion\ContentTypeIdentifier(['landing_page', 'folder'])]);
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

        $this->displaySearchResult($this->searchService->findContentInfo($query), $output, 'SearchService::findContentInfo()');
        $this->displaySearchResult($this->searchService->findContent($query), $output, 'SearchService::findContent()');
        $this->displaySearchResult($this->searchService->findLocations($query), $output, 'SearchService::findLocations()');

        //return Command::SUCCESS;

        $filter = new Filter(new Query\Criterion\ContentTypeIdentifier(['landing_page', 'folder']), [
            new CustomQuery\SortClause\ContentTypeIdentifier(Query::SORT_DESC),
            new Query\SortClause\ContentName(Query::SORT_DESC)
        ]);

        $this->displayFilterResult($this->contentService->find($filter), $output, 'ContentService::find()');
        $this->displayFilterResult($this->locationService->find($filter), $output, 'LocationService::find()');

        return Command::SUCCESS;
    }

    private function displaySearchResult(SearchResult $searchResult, OutputInterface $output, $title = 'Search Result') {
        $output->writeln("<info>= $title =</info>");
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

    private function displayFilterResult(ContentList|LocationList $list, OutputInterface $output, $title = 'Filter Result') {
        $output->writeln("<info>= $title =</info>");
        switch (get_class($list)) {
            case LocationList::class:
                /** @var Location $location */
                foreach ($list->getIterator() as $location) {
                    $output->writeln("{$location->getContentInfo()->getName()} ({$location->getContentInfo()->getContentType()->getIdentifier()})");
                }
                break;
            case ContentList::class:
                /** @var Content $content */
                foreach ($list->getIterator() as $content) {
                    $output->writeln("{$content->getName()} ({$content->getContentType()->getIdentifier()})");
                }
                break;
        }
    }
}
