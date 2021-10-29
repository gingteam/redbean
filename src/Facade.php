<?php

declare(strict_types=1);

namespace GingTeam\RedBean;

use RedBeanPHP\Facade as RedBeanPHPFacade;

final class Facade extends RedBeanPHPFacade
{
    public static function createQueryBuilder()
    {
        return new QueryBuilder(self::getDatabaseAdapter());
    }
}
