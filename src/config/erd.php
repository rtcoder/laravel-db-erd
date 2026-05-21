<?php

declare(strict_types=1);

return [
    /**
     * pgsql - PostgreSQL
     * mysql - MySQL
     * sqlite - SQLite
     * sqlsrv - Microsoft SQL Server
     * oracle - Oracle database
     */
    'default_driver' => env('DB_ERD_DRIVER', env('DB_CONNECTION', 'pgsql')),
    'output_directory' => storage_path('erd'),
    'output_name' => 'erd_diagram',
    /**
     * Supported formats:
     * - png
     * - pdf
     * - svg
     * - html
     */
    'output_format' => 'pdf',
    'exclude_tables' => ['migrations', 'jobs', 'failed_jobs'],
];
