<?php

declare(strict_types=1);

use GingTeam\RedBean\Facade as R;
use RedBeanPHP\OODBBean;

if (!function_exists('model')) {
    /** @return array<int,OODBBean<mixed>>|OODBBean<mixed> */
    function model(string $name, int $number = 1): array|OODBBean
    {
        return R::dispense($name, $number);
    }
}
