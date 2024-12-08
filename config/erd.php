<?php

return [
    'exclude_tables' => ['migrations', 'jobs', 'failed_jobs'],
    /**
     * Supported formats:
     * - png
     * - pdf
     * - svg
     */
    'output_format' => 'pdf',
    'output_path' => storage_path('erd'),
    'output_name' => 'erd_diagram',
    /**
     * psql - PostgreSQL
     * mysql - MySQL
     * sqlite - SQLite
     * sqlsrv - Microsoft SQL Server
     */
    'connection' => 'psql'
];
