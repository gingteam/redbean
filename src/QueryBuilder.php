<?php

declare(strict_types=1);

namespace GingTeam\RedBean;

use RedBeanPHP\Adapter;

final class QueryBuilder
{
    private Adapter $adapter;

    private array $sql = [];

    private array $params = [];

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function __call(string $name, array $args = [])
    {
        $name = strtoupper(implode(' ', preg_split('/(?=[A-Z])/', $name)));
        $this->sql[] = $name.' '.implode(',', $args);

        return $this;
    }

    public function put($param)
    {
        $this->params[] = $param;

        return $this;
    }

    public function get($what = '')
    {
        $what = 'get'.ucfirst($what);
        $result = $this->adapter->$what($this->toSql(), $this->getBindings());
        $this->reset();

        return $result;
    }

    public function reset()
    {
        $this->sql = [];
        $this->params = [];

        return $this;
    }

    public function raw(string $sql)
    {
        $this->sql[] = $sql;

        return $this;
    }

    public function dump()
    {
        $list = [$this->toSql(), $this->getBindings()];
        $this->reset();

        return $list;
    }

    public function nest(QueryBuilder $qb)
    {
        [$sql, $bindings] = $qb->dump();

        $this->sql[] = $sql;

        $this->params += $bindings;

        return $this;
    }

    public function open()
    {
        $this->sql[] = '(';

        return $this;
    }

    public function close()
    {
        $this->sql[] = ')';

        return $this;
    }

    public function slots(int $number)
    {
        return implode(',', array_fill(0, $number > 0 ? $number : 0, '?'));
    }

    public function create()
    {
        return new self($this->adapter);
    }

    public function toSql(): string
    {
        return implode(' ', $this->sql);
    }

    public function getBindings(): array
    {
        return $this->params;
    }
}
