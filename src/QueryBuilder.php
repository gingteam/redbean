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
        $result = $this->adapter->$what($this->sql(), $this->binding());
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
        $list = [$this->sql(), $this->binding()];
        $this->end();

        return $list;
    }

    public function nest(QueryBuilder $qb)
    {
        [$sql, $params] = $qb->dump();

        $this->sql[] = $sql;

        $this->params += $params;

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

    private function sql(): string
    {
        return implode(' ', $this->sql);
    }

    private function binding(): array
    {
        return $this->params;
    }
}
