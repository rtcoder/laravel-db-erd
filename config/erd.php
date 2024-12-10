<?php

return [
    /**
     * psql - PostgreSQL
     * mysql - MySQL
     * sqlite - SQLite
     * sqlsrv - Microsoft SQL Server
     * oracle - Oracle database
     */
    'default_driver' => env('DB_ERD_DRIVER', 'psql'),
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
