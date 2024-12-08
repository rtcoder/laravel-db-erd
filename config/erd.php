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
     */
    'connection' => 'psql'
];
