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

    public function __call(string $name, array $args = []): self
    {
        $name = strtoupper(implode(' ', preg_split('/(?=[A-Z])/', $name)));
        $this->sql[] = $name.' '.implode(', ', $args);

        return $this;
    }

    public function put($param): self
    {
        $this->params[] = $param;

        return $this;
    }

    public function fetch(): array
    {
        $result = $this->adapter->get($this->toSql(), $this->getBindings());
        $this->reset();

        return $result;
    }

    public function fetchSingle(): array
    {
        $result = $this->adapter->getRow($this->toSql(), $this->getBindings());
        $this->reset();

        return $result;
    }

    public function fetchFirstColumn(): array
    {
        $result = $this->adapter->getCol($this->toSql(), $this->getBindings());
        $this->reset();

        return $result;
    }

    public function getLastInsertId(): int
    {
        return $this->adapter->getInsertID();
    }

    public function execute(): void
    {
        $this->adapter->exec($this->toSql(), $this->getBindings());
        $this->reset();
    }

    public function reset(): self
    {
        $this->sql = [];
        $this->params = [];

        return $this;
    }

    public function raw(string $sql): self
    {
        $this->sql[] = $sql;

        return $this;
    }

    public function dump(): array
    {
        $list = [$this->toSql(), $this->getBindings()];
        $this->reset();

        return $list;
    }

    public function nest(QueryBuilder $qb): self
    {
        [$sql, $bindings] = $qb->dump();

        $this->sql[] = $sql;

        $this->params += $bindings;

        return $this;
    }

    public function open(): self
    {
        $this->sql[] = '(';

        return $this;
    }

    public function close(): self
    {
        $this->sql[] = ')';

        return $this;
    }

    public function slots(int $number): string
    {
        return implode(', ', array_fill(0, $number > 0 ? $number : 0, '?'));
    }

    public function create(): QueryBuilder
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
