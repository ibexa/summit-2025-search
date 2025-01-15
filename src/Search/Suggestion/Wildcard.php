<?php declare(strict_types=1);

namespace App\Search\Suggestion;

use Ibexa\Contracts\Search\Event\BuildSuggestionCollectionEvent;
use Ibexa\Search\EventDispatcher\EventListener\ContentSuggestionSubscriber;
use Ibexa\Search\Model\SuggestionQuery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Wildcard implements EventSubscriberInterface
{
    private ContentSuggestionSubscriber $originalSubscriber;

    public function __construct(ContentSuggestionSubscriber$originalSubscriber)
    {
        $this->originalSubscriber = $originalSubscriber;
    }

    /** @return array<string, string> */
    public static function getSubscribedEvents(): array
    {
        return [BuildSuggestionCollectionEvent::class => 'onBuildSuggestionCollectionEvent'];
    }

    public function onBuildSuggestionCollectionEvent(BuildSuggestionCollectionEvent $event): BuildSuggestionCollectionEvent
    {
        $suggestionQuery = $event->getQuery();

        $temporarySuggestionCollection = $this->originalSubscriber->onBuildSuggestionCollectionEvent(
            new BuildSuggestionCollectionEvent(
                new SuggestionQuery(
                    trim(preg_replace('/\**[ ]+/', '* ', "{$suggestionQuery->getQuery()}* ")),
                    $suggestionQuery->getLimit(),
                    $suggestionQuery->getLanguageCode(),
                )
            )
        )->getSuggestionCollection();

        $currentSuggestionCollection = $event->getSuggestionCollection();
        foreach ($temporarySuggestionCollection as $suggestion) {
            $currentSuggestionCollection->append($suggestion);
        }
        $currentSuggestionCollection->increaseTotalCount($temporarySuggestionCollection->getTotalCount() - $currentSuggestionCollection->getTotalCount());

        return $event;
    }
}
