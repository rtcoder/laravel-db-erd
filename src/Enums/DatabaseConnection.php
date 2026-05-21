<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Enums;

enum DatabaseConnection: string
{
    case Pgsql = 'pgsql';
    case Mysql = 'mysql';
    case Sqlite = 'sqlite';
    case SqlServer = 'sqlsrv';
    case Oracle = 'oracle';

    public static function fromConnectionName(string $connection): ?self
    {
        return match (strtolower($connection)) {
            'pgsql', 'postgres', 'postgresql', 'psql' => self::Pgsql,
            'mysql' => self::Mysql,
            'sqlite' => self::Sqlite,
            'sqlsrv' => self::SqlServer,
            'oracle' => self::Oracle,
            default => null,
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $connection): string => $connection->value,
            self::cases()
        );
    }

    /**
     * @return array<int, string>
     */
    public static function aliases(): array
    {
        return ['postgres', 'postgresql', 'psql'];
    }
}
