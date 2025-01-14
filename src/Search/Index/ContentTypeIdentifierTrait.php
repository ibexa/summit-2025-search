<?php

declare(strict_types=1);

namespace App\Search\Index;

use Ibexa\Contracts\Core\Search\FieldType;

trait ContentTypeIdentifierTrait
{
    const string FIELD_NAME = 'content_type_identifier';
    const string FIELD_IDENTIFIER = 'content_type_identifier_s';

    static public function getFieldType(): FieldType
    {
        //return new FieldType\IdentifierField(); // Doesn't accept underscores, silently remove them (e.g. `content_type_identifier_id: landingpage`).
        return new FieldType\StringField();
    }
}
