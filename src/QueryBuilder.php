<?php

declare(strict_types=1);

namespace GingTeam\RedBean;

use RedBeanPHP\Adapter;

final class QueryBuilder
{
    private Adapter $adapter;

    private bool $running = false;

    private array $sql = [];

    private array $params = [];

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function __call(string $name, array $args = [])
    {
        $name = strtoupper(implode(' ', preg_split('/(?=[A-Z])/', $name)));

        if ($this->running) {
            $this->sql[] = $name.' '.implode(',', $args);

            return $this;
        } else {
            return $this->adapter->getCell('SELECT '.$name.'('.implode(',', $args).')');
        }
    }

    public function start()
    {
        $this->running = true;

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
        $this->end();

        return $result;
    }

    public function end()
    {
        $this->sql = [];
        $this->params = [];
        $this->running = false;

        return $this;
    }

    public function addRawSQL(string $sql)
    {
        if ($this->running) {
            $this->sql[] = $sql;
        }

        return $this;
    }

    public function dump()
    {
        $list = [$this->toSql(), $this->getBindings()];
        $this->end();

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
        if ($this->running) {
            $this->sql[] = '(';
        }

        return $this;
    }

    public function close()
    {
        if ($this->running) {
            $this->sql[] = ')';
        }

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
