<?php

namespace App\Command;

use App\Search\Query as CustomQuery;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SearchCommand extends Command
{
    protected static $defaultName = 'app:search';
    private SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
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
        /* */
        $query = new Query([
            'filter' => new Query\Criterion\ContentTypeIdentifier(['landing_page', 'folder']),
            'sortClauses' => [
                new CustomQuery\SortClause\ContentTypeIdentifier(/* * /Query::SORT_DESC/**/),
                new Query\SortClause\ContentName(),
            ],
            //'aggregations' => [new Query\Aggregation\ContentTypeTermAggregation('Content types')],
            'aggregations' => [new CustomQuery\Aggregation\ContentTypeIdentifier('Content types')],
            'limit' => 100,
        ]);
        /* */
        $searchResult = $this->searchService->findContentInfo($query);
        foreach ($searchResult->searchHits as $searchHit) {
            $scorePercent = $searchResult->maxScore ?
                str_pad(round(100 * $searchHit->score / $searchResult->maxScore), 3, ' ', STR_PAD_LEFT) . '% '
                : '';
            /** @var ContentInfo $contentInfo */
            $contentInfo = $searchHit->valueObject;
            $output->writeln("{$scorePercent}{$contentInfo->getName()} [{$contentInfo->getContentType()->getName()} ({$contentInfo->getContentType()->getIdentifier()})]");
        }
        if (!empty($searchResult->aggregations)) {
            /** @var AggregationResult\TermAggregationResult $aggregation */
            foreach ($searchResult->aggregations as $aggregation) {
                echo "-- {$aggregation->getName()} --\n";
                /** @var AggregationResult\TermAggregationResultEntry $entry */
                foreach ($aggregation->getEntries() as $entry) {
                    echo "{$entry->getKey()->getName()} ({$entry->getKey()->getIdentifier()}): {$entry->getCount()}\n";
                }
            }
        }

        return Command::SUCCESS;
    }
}
