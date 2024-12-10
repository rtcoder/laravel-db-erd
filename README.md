# Laravel DB ERD Generator

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)  
A Laravel package for generating Entity-Relationship Diagrams (ERD) of your database schema. This tool is designed to support multiple database systems like MySQL, PostgreSQL, SQLite, SQL Server, and Oracle.

---

## Features

- Automatically scans your database schema for tables and their relationships.
- Generates ERD diagrams in PDF format using Graphviz.
- Extensible design with support for multiple database drivers.

---

## Installation

### Requirements
- PHP 8.1 or higher
- Laravel 9.x or higher
- [Graphviz](https://graphviz.org/) installed on your system

### Step 1: Install the package
```bash
composer require rtcoder/laravel-db-erd
```

### Step 2: Publish the configuration (optional)
If you need to customize the behavior, publish the configuration file:

```bash
php artisan vendor:publish --tag=db-erd-config
```

### Step 3: Install Graphviz
Ensure Graphviz is installed on your system.

#### On macOS:
```bash
brew install graphviz
```

#### On Ubuntu:
```bash
sudo apt install graphviz
```

#### On Windows:
Download and install from [Graphviz's official site](https://graphviz.org/).

---

## Usage
**Generate an ERD** Run the following Artisan command to generate the ERD:
```bash
php artisan erd:generate --output=storage/erd/erd_diagram.pdf --driver=mysql
```
The `--output` option specifies the file path for the generated diagram.

**Supported Databases**
* MySQL
* PostgreSQL
* SQLite
* SQL Server
* Oracle

**Supported output formats**
* PDF
* SVG
* PNG
* HTML

## Configuration
You can customize the package by modifying the configuration file (`config/db-erd.php`):
```php
return [
    'default_driver' => env('DB_ERD_DRIVER', 'mysql'),
    'output_directory' => storage_path('erd'),
    'output_name' => 'erd_diagram',
    'output_format' => 'pdf',
    'exclude_tables' => ['migrations', 'jobs', 'failed_jobs'],
];
```

---

## Troubleshooting
1. `Graphviz not found` Error\
Ensure Graphviz is correctly installed and added to your system's PATH.

2. Empty ERD Diagram\
Verify that your database schema has relationships (foreign keys).

---
## License
This package is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
