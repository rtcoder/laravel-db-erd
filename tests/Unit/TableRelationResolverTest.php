<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Rtcoder\LaravelERD\Exceptions\InvalidConnectionNameException;
use Rtcoder\LaravelERD\Services\TableRelation\MysqlTableRelation;
use Rtcoder\LaravelERD\Services\TableRelation\OracleTableRelation;
use Rtcoder\LaravelERD\Services\TableRelation\PostgresTableRelation;
use Rtcoder\LaravelERD\Services\TableRelation\SqliteTableRelation;
use Rtcoder\LaravelERD\Services\TableRelation\SqlServerTableRelation;
use Rtcoder\LaravelERD\Services\TableRelation\TableRelationResolver;

class TableRelationResolverTest extends TestCase
{
    public function test_postgres_resolver(): void
    {
        $resolver = new TableRelationResolver();
        $resolver->setConnection('psql');
        $classInstance = $resolver->resolve();
        $this->assertInstanceOf(PostgresTableRelation::class, $classInstance);
    }

    public function test_pgsql_alias_resolver(): void
    {
        $resolver = new TableRelationResolver();
        $resolver->setConnection('pgsql');
        $classInstance = $resolver->resolve();
        $this->assertInstanceOf(PostgresTableRelation::class, $classInstance);
    }

    public function test_mysql_resolver(): void
    {
        $resolver = new TableRelationResolver();
        $resolver->setConnection('mysql');
        $classInstance = $resolver->resolve();
        $this->assertInstanceOf(MysqlTableRelation::class, $classInstance);
    }

    public function test_sqlite_resolver(): void
    {
        $resolver = new TableRelationResolver();
        $resolver->setConnection('sqlite');
        $classInstance = $resolver->resolve();
        $this->assertInstanceOf(SqliteTableRelation::class, $classInstance);
    }

    public function test_sql_server_resolver(): void
    {
        $resolver = new TableRelationResolver();
        $resolver->setConnection('sqlsrv');
        $classInstance = $resolver->resolve();
        $this->assertInstanceOf(SqlServerTableRelation::class, $classInstance);
    }

    public function test_oracle_resolver(): void
    {
        $resolver = new TableRelationResolver();
        $resolver->setConnection('oracle');
        $classInstance = $resolver->resolve();
        $this->assertInstanceOf(OracleTableRelation::class, $classInstance);
    }

    public function test_invalid_connection_throws_clear_exception(): void
    {
        $resolver = new TableRelationResolver();
        $resolver->setConnection('mongodb');

        $this->expectException(InvalidConnectionNameException::class);
        $this->expectExceptionMessage('Unsupported database driver [mongodb].');

        $resolver->resolve();
    }
}
