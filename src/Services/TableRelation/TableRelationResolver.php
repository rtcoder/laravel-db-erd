<?php

namespace Rtcoder\LaravelERD\Services\TableRelation;

use Rtcoder\LaravelERD\Services\TableRelation\Exception\InvalidConnectionNameException;
use Rtcoder\LaravelERD\Services\TableRelation\Interface\TableRelationInterface;

class TableRelationResolver
{
    private string $db_connection;

    public function setConnection(string $connection): void
    {
        $this->db_connection = $connection;
    }

    /**
     * @return TableRelationInterface
     * @throws InvalidConnectionNameException
     */
    public function resolve(): TableRelationInterface
    {
        return match ($this->db_connection) {
            'psql' => new PostgresTableRelation(),
            'mysql' => new MysqlTableRelation(),
            'sqlite' => new SqliteTableRelation(),
            'sqlsrv' => new SqlServerTableRelation(),
            default => throw new InvalidConnectionNameException()
        };
    }
}
