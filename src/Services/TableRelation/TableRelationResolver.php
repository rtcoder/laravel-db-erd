<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Services\TableRelation;

use Rtcoder\LaravelERD\Exceptions\InvalidConnectionNameException;
use Rtcoder\LaravelERD\Services\TableRelation\Interface\TableRelationInterface;

class TableRelationResolver
{
    private ?string $dbConnection = null;

    public function setConnection(string $connection): void
    {
        $this->dbConnection = strtolower($connection);
    }

    /**
     * @return TableRelationInterface
     * @throws InvalidConnectionNameException
     */
    public function resolve(): TableRelationInterface
    {
        return match ($this->dbConnection) {
            'pgsql', 'postgres', 'postgresql', 'psql' => new PostgresTableRelation(),
            'mysql' => new MysqlTableRelation(),
            'sqlite' => new SqliteTableRelation(),
            'sqlsrv' => new SqlServerTableRelation(),
            'oracle' => new OracleTableRelation(),
            default => throw InvalidConnectionNameException::forConnection($this->dbConnection),
        };
    }
}
