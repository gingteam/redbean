<?php

declare(strict_types=1);

namespace GingTeam\RedBean;

use RedBeanPHP\Facade as RedBeanPHPFacade;
use RedBeanPHP\RedException;

final class Facade extends RedBeanPHPFacade
{
    public static function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder(self::getDatabaseAdapter());
    }

    /**
     * @param array<mixed> $bindings
     *
     * @throws RedException
     */
    public static function findOneOr(
        string $type,
        callable $callable,
        ?string $sql,
        array $bindings = []
    ): mixed {
        $bean = self::findOneOrDispense($type, $sql, $bindings);

        // @phpstan-ignore-next-line
        if (!$bean->id) {
            return $callable($bean);
        }

        return $bean;
    }
}
