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
        $this->sql[] = $name.' '.implode(', ', $args);

        return $this;
    }

    public function insert(string $table, array $columns)
    {
        $this->sql[] = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            $this->slots(count($columns))
        );

        return $this;
    }

    public function update(string $table, array $columns)
    {
        array_walk($columns, static function (&$column) {
            $column .= ' = ?';
        });

        $this->sql[] = sprintf(
            'UPDATE %s SET %s',
            $table,
            implode(', ', $columns)
        );

       return $this;
    }

    public function put($param)
    {
        $this->params[] = $param;

        return $this;
    }

    public function get()
    {
        $result = $this->adapter->get($this->toSql(), $this->getBindings());
        $this->reset();

        return $result;
    }

    public function getOne()
    {
        $result = $this->adapter->getRow($this->toSql(), $this->getBindings());
        $this->reset();

        return $result;
    }

    public function getLastInsertId(): int
    {
        return $this->adapter->getInsertID();
    }

    public function execute()
    {
        $this->adapter->exec($this->toSql(), $this->getBindings());
        $this->reset();
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
        return implode(', ', array_fill(0, $number > 0 ? $number : 0, '?'));
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
