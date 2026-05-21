<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Services\TableRelation;

use Rtcoder\LaravelERD\Enums\DatabaseConnection;
use Rtcoder\LaravelERD\Exceptions\InvalidConnectionNameException;
use Rtcoder\LaravelERD\Services\TableRelation\Interface\TableRelationInterface;

class TableRelationResolver
{
    private ?DatabaseConnection $dbConnection = null;
    private ?string $connectionName = null;

    public function setConnection(string $connection): void
    {
        $this->connectionName = strtolower($connection);
        $this->dbConnection = DatabaseConnection::fromConnectionName($connection);
    }

    /**
     * @return TableRelationInterface
     * @throws InvalidConnectionNameException
     */
    public function resolve(): TableRelationInterface
    {
        return match ($this->dbConnection) {
            DatabaseConnection::Pgsql => new PostgresTableRelation(),
            DatabaseConnection::Mysql => new MysqlTableRelation(),
            DatabaseConnection::Sqlite => new SqliteTableRelation(),
            DatabaseConnection::SqlServer => new SqlServerTableRelation(),
            DatabaseConnection::Oracle => new OracleTableRelation(),
            default => throw InvalidConnectionNameException::forConnection($this->connectionName),
        };
    }
}
