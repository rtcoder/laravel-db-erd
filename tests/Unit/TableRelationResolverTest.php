<?php

namespace Tests\Unit;


use Exception;
use PHPUnit\Framework\TestCase;
use Rtcoder\LaravelERD\Services\TableRelation\MysqlTableRelation;
use Rtcoder\LaravelERD\Services\TableRelation\OracleTableRelation;
use Rtcoder\LaravelERD\Services\TableRelation\PostgresTableRelation;
use Rtcoder\LaravelERD\Services\TableRelation\SqliteTableRelation;
use Rtcoder\LaravelERD\Services\TableRelation\SqlServerTableRelation;
use Rtcoder\LaravelERD\Services\TableRelation\TableRelationResolver;

class TableRelationResolverTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function test_postgres_resolver(): void
    {
        $resolver = new TableRelationResolver();
        $resolver->setConnection('psql');
        $classInstance = $resolver->resolve();
        $instanceClassName = get_class($classInstance);
        $tableRelationClassName = PostgresTableRelation::class;
        $this->assertSame($tableRelationClassName, $instanceClassName);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function test_mysql_resolver(): void
    {
        $resolver = new TableRelationResolver();
        $resolver->setConnection('mysql');
        $classInstance = $resolver->resolve();
        $instanceClassName = get_class($classInstance);
        $tableRelationClassName = MysqlTableRelation::class;
        $this->assertSame($tableRelationClassName, $instanceClassName);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function test_sqlite_resolver(): void
    {
        $resolver = new TableRelationResolver();
        $resolver->setConnection('sqlite');
        $classInstance = $resolver->resolve();
        $instanceClassName = get_class($classInstance);
        $tableRelationClassName = SqliteTableRelation::class;
        $this->assertSame($tableRelationClassName, $instanceClassName);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function test_sql_server_resolver(): void
    {
        $resolver = new TableRelationResolver();
        $resolver->setConnection('sqlsrv');
        $classInstance = $resolver->resolve();
        $instanceClassName = get_class($classInstance);
        $tableRelationClassName = SqlServerTableRelation::class;
        $this->assertSame($tableRelationClassName, $instanceClassName);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function test_oracle_resolver(): void
    {
        $resolver = new TableRelationResolver();
        $resolver->setConnection('oracle');
        $classInstance = $resolver->resolve();
        $instanceClassName = get_class($classInstance);
        $tableRelationClassName = OracleTableRelation::class;
        $this->assertSame($tableRelationClassName, $instanceClassName);
    }
}
