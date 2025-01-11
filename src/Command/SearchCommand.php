<?php

namespace App\Command;

use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
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

        $query = new Query(['query' => new Query\Criterion\FullText($text)]);
        //$query = new Query(['query' => new Query\Criterion\ContentTypeIdentifier('folder')]);
        $searchResult = $this->searchService->findContent($query);
        foreach ($searchResult->searchHits as $searchHit) {
            $scorePercent = $searchResult->maxScore ?
                str_pad(round(100 * $searchHit->score / $searchResult->maxScore), 3, ' ', STR_PAD_LEFT) . '% '
                : '';
            /** @var Content $content */
            $content = $searchHit->valueObject;
            $output->writeln("{$scorePercent}{$content->getName()}");
        }

        return Command::SUCCESS;

        $locationQuery = new LocationQuery(['query' => new Query\Criterion\FullText($text)]);
        //$locationQuery = new LocationQuery(['filter' => new Query\Criterion\ContentTypeIdentifier('folder')]);
        $searchResult = $this->searchService->findLocations($locationQuery);
        foreach ($searchResult->searchHits as $searchHit) {
            $scorePercent = $searchResult->maxScore ?
                str_pad(round(100 * $searchHit->score / $searchResult->maxScore), 3, ' ', STR_PAD_LEFT) . '% '
                : '';
            /** @var Location $location */
            $location = $searchHit->valueObject;
            $output->writeln("{$scorePercent}{$location->contentInfo->getName()}");
        }

        return Command::SUCCESS;
    }
}
