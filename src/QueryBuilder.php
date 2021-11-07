<?php

declare(strict_types=1);

namespace GingTeam\RedBean;

use RedBeanPHP\Adapter;

final class QueryBuilder
{
    private Adapter $adapter;

    /** @var array<int,string> */
    private array $sql = [];

    /** @var array<int,float|int|string> */
    private array $params = [];

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /** @param array<int,string> $args */
    public function __call(string $name, array $args = []): self
    {
        $name = strtoupper(implode(' ', preg_split('/(?=[A-Z])/', $name)));
        $this->sql[] = $name.' '.implode(', ', $args);

        return $this;
    }

    public function put(float|int|string $param): self
    {
        $this->params[] = $param;

        return $this;
    }

    /** @return array<int,array<string,string>> */
    public function fetch(): array
    {
        $result = $this->adapter->get($this->toSql(), $this->getBindings());
        $this->reset();

        return $result;
    }

    /** @return array<string,string> */
    public function fetchSingle(): array
    {
        $result = $this->adapter->getRow($this->toSql(), $this->getBindings());
        $this->reset();

        return $result;
    }

    /** @return array<int,string> */
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

    /** @return array{0:string,1:array<int,float|int|string>} */
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

    /** @return array<int,float|int|string> */
    public function getBindings(): array
    {
        return $this->params;
    }
}
