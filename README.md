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
- Laravel 10.x or higher
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
If you need to customize the views, you can publish them using:

```bash
php artisan vendor:publish --tag=db-erd-views
```
This will copy the default views to your project's `resources/views/vendor/laravel-erd` directory, where you can modify them as needed.

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

**Check Graphviz availability**
```bash
php artisan erd:doctor
```
The command verifies that the Graphviz `dot` binary is available in PATH and prints its detected path and version.

**Supported Databases**
* MySQL
* PostgreSQL
* SQLite
* SQL Server
* Oracle

Available database drivers are represented by the `Rtcoder\LaravelERD\Enums\DatabaseConnection` enum. PostgreSQL also accepts `postgres`, `postgresql`, and `psql` aliases.

**Supported output formats**
* PDF
* SVG
* PNG
* HTML

Available output formats are represented by the `Rtcoder\LaravelERD\Enums\OutputFormat` enum.

## Configuration
You can customize the package by modifying the configuration file (`config/erd.php`):
```php
return [
    'default_driver' => env('DB_ERD_DRIVER', env('DB_CONNECTION', 'pgsql')),
    'output_directory' => storage_path('erd'),
    'output_name' => 'erd_diagram',
    'output_format' => 'pdf',
    'exclude_tables' => ['migrations', 'jobs', 'failed_jobs'],
];
```

## Exceptions
The package exposes concrete exceptions so you can handle generator failures without catching a generic `Exception`.

| Exception | When it is thrown |
| --- | --- |
| `Rtcoder\LaravelERD\Exceptions\UnsupportedOutputFormatException` | The output file extension is not one of `pdf`, `png`, `svg`, or `html`. |
| `Rtcoder\LaravelERD\Exceptions\InvalidConnectionNameException` | The selected database driver is not supported. |
| `Rtcoder\LaravelERD\Exceptions\DirectoryCreationException` | The output directory cannot be created. |
| `Rtcoder\LaravelERD\Exceptions\TemporaryFileCreationException` | The temporary DOT file for Graphviz cannot be created. |
| `Rtcoder\LaravelERD\Exceptions\FileWriteException` | The generated DOT or HTML file cannot be written. |
| `Rtcoder\LaravelERD\Exceptions\GraphvizRenderException` | Graphviz exits with a non-zero status while rendering PDF, PNG, or SVG output. |
| `Rtcoder\LaravelERD\Exceptions\ViewNotFoundException` | The HTML diagram Blade view cannot be found in the application or package paths. |
| `Rtcoder\LaravelERD\Exceptions\ERDException` | Base exception for runtime generation failures. |

Example:

```php
use Rtcoder\LaravelERD\Exceptions\ERDException;
use Rtcoder\LaravelERD\Exceptions\InvalidConnectionNameException;
use Rtcoder\LaravelERD\Exceptions\UnsupportedOutputFormatException;
use Rtcoder\LaravelERD\Services\ERDGenerator;

try {
    app(ERDGenerator::class)->generate(storage_path('erd/schema.svg'), 'pgsql');
} catch (UnsupportedOutputFormatException|InvalidConnectionNameException $exception) {
    report($exception);
} catch (ERDException $exception) {
    report($exception);
}
```

---

## Troubleshooting
### `Graphviz not found` error
Ensure Graphviz is correctly installed and the `dot` binary is available in your system's PATH.

You can check this from Laravel with:
```bash
php artisan erd:doctor
```

#### macOS
```bash
brew install graphviz
```

#### Ubuntu / Debian
```bash
sudo apt install graphviz
```

#### Windows
Install Graphviz from the [official download page](https://graphviz.org/download/), or use one of the package manager commands below:

```powershell
winget install graphviz
```

```powershell
choco install graphviz
```

If the command still cannot find `dot`, restart your terminal and verify that the Graphviz `bin` directory is included in PATH.

### Empty ERD Diagram
Verify that your database schema has relationships (foreign keys).

---
## License
This package is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
