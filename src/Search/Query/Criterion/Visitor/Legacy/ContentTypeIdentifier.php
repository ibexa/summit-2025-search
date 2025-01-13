<?php

declare(strict_types=1);

namespace App\Search\Query\Criterion\Visitor\Legacy;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Core\Persistence\Legacy\Content\Type\Gateway as ContentTypeGateway;
use Ibexa\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use Ibexa\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

class ContentTypeIdentifier extends CriterionHandler
{

    public function accept(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\ContentTypeIdentifier;
    }

    public function handle(CriteriaConverter $converter, QueryBuilder $queryBuilder, Criterion $criterion, array $languageSettings)
    {
        /* * /
        $subSelect = $this->connection->createQueryBuilder();
        $subSelect->select(['id'])
            ->from(ContentTypeGateway::CONTENT_TYPE_TABLE, 'ct')
            ->where(
                $queryBuilder->expr()->in(
                    'ct.identifier',
                    $queryBuilder->createNamedParameter($criterion->value, Connection::PARAM_STR_ARRAY)
                )
            );

        return $queryBuilder->expr()->in(
            'c.contentclass_id',
            $subSelect->getSQL()
        );
        /* */
        if (!$this->hasJoinedTableAs($queryBuilder, 'ct')) {
            $queryBuilder->innerJoin(
                'c',
                ContentTypeGateway::CONTENT_TYPE_TABLE,
                'ct',
                'c.contentclass_id = ct.id'
            );
        }

        return $queryBuilder->expr()->in(
            'ct.identifier',
            $queryBuilder->createNamedParameter($criterion->value, Connection::PARAM_STR_ARRAY)
        );
        /* */
    }
}
