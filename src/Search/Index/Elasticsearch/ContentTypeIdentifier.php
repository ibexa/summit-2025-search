<?php

declare(strict_types=1);

namespace App\Search\Index\Elasticsearch;

use App\Search\Index\ContentTypeIdentifierTrait;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Search\Field;
use Ibexa\Contracts\Core\Search\FieldType\IdentifierField;
use Ibexa\Contracts\Elasticsearch\Mapping\Event\ContentIndexCreateEvent;
use Ibexa\Contracts\Elasticsearch\Mapping\Event\LocationIndexCreateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContentTypeIdentifier implements EventSubscriberInterface
{
    use ContentTypeIdentifierTrait;

    private ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /** @return array<string, string> */
    public static function getSubscribedEvents(): array
    {
        return [
            ContentIndexCreateEvent::class => 'onIndexCreate',
            LocationIndexCreateEvent::class => 'onIndexCreate',
        ];
    }

    public function onIndexCreate(ContentIndexCreateEvent|LocationIndexCreateEvent $event): void
    {
        $document = $event->getDocument();
        $contentTypeIdentifier = $this->contentTypeService->loadContentType($document->contentTypeId)->getIdentifier();
        $document->fields[] = new Field(self::FIELD_NAME, $contentTypeIdentifier, self::getFieldType());
    }
}
