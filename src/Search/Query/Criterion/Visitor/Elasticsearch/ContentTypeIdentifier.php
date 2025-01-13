<?php

declare(strict_types=1);

namespace App\Search\Query\Criterion\Visitor\Elasticsearch;

use App\Search\Index\ContentTypeIdentifierTrait;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Elasticsearch\Query\CriterionVisitor;
use Ibexa\Contracts\Elasticsearch\Query\LanguageFilter;
use Ibexa\Elasticsearch\Query\CriterionVisitor\AbstractTermsVisitor;

class ContentTypeIdentifier extends AbstractTermsVisitor implements CriterionVisitor
{
    use ContentTypeIdentifierTrait;

    public function supports(Criterion $criterion, LanguageFilter $languageFilter): bool
    {
        return $criterion instanceof Criterion\ContentTypeIdentifier;
    }

    protected function getTargetField(Criterion $criterion): string
    {
        return self::FIELD_IDENTIFIER;
    }
}
