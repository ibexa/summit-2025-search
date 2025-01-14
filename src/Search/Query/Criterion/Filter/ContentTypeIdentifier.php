<?php

declare(strict_types=1);

namespace App\Search\Query\Criterion\Filter;

use Doctrine\DBAL\Connection;
use Ibexa\Contracts\Core\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Filter\CriterionQueryBuilder;
use Ibexa\Contracts\Core\Repository\Values\Filter\FilteringCriterion;
use Ibexa\Core\Persistence\Legacy\Content\Type\Gateway as ContentTypeGateway;

class ContentTypeIdentifier implements CriterionQueryBuilder
{

    public function accepts(FilteringCriterion $criterion): bool
    {
        return $criterion instanceof Criterion\ContentTypeIdentifier;
    }

    public function buildQueryConstraint(FilteringQueryBuilder $queryBuilder, FilteringCriterion $criterion): ?string
    {
        //dump('\App\Search\Query\Criterion\Filter\ContentTypeIdentifier::buildQueryConstraint');
        $subSelect = $queryBuilder->getConnection()->createQueryBuilder();
        $subSelect
            ->select(['id'])
            ->from(ContentTypeGateway::CONTENT_TYPE_TABLE)
            ->where(
                $queryBuilder->expr()->in(
                    'identifier',
                    $queryBuilder->createNamedParameter($criterion->value, Connection::PARAM_STR_ARRAY)
                )
            )
        ;

        return $queryBuilder->expr()->in(
            'content.contentclass_id',
            $subSelect->getSQL()
        );
    }
}
