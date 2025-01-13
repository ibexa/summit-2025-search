<?php

declare(strict_types=1);

namespace App\Search\Index\Solr;

use App\Search\Index\ContentTypeIdentifierTrait;
use Ibexa\Contracts\Core\Persistence\Content as SPIContent;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Search\Field;
use Ibexa\Contracts\Core\Search\FieldType\IdentifierField;
use Ibexa\Contracts\Solr\FieldMapper\ContentFieldMapper;

class ContentTypeIdentifier extends ContentFieldMapper
{
    use ContentTypeIdentifierTrait;

    private ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    public function accept(SPIContent $content): bool
    {
        return true;
    }

    /** @return array<int, Field> */
    public function mapFields(SPIContent $content): array
    {
        $contentTypeIdentifier = $this->contentTypeService->loadContentType($content->versionInfo->contentInfo->contentTypeId)->getIdentifier();
        return [
            new Field(self::FIELD_NAME, $contentTypeIdentifier, self::getFieldType()),
        ];
    }
}
