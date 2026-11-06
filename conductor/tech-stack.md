# Tech Stack: DISC Personality Test

## Core Languages
- **PHP**: Core backend language, supporting broad compatibility for PHP 8.3+
- **SQL**: Database schema and queries targeting MySQL 5.7+ / 8.0+ and MariaDB 10.2+.
- **HTML5 & CSS3**: Semantic markup and lightweight, responsive styling.
- **JavaScript**: Client-side interactions and validation (vanilla JS).

## Architecture & Frameworks
- **Backend Architecture**: Native PHP with PSR-4 autoloading (`Disc\` namespace mapped to `src/`). Zero external framework dependencies.
- **Configuration & Environment**: Custom zero-dependency `.env` loader (`conf/autoload_env.php`) and centralized PDO configuration with fallback protection (`conf/config.php`).
- **Frontend Architecture**: Server-rendered HTML templates with template caching in `cache/` and vanilla client-side assets in `assets/`.
- **Database Layer**: PDO with prepared statements, single round-trip `UNION ALL` fallbacks, and connection error masking.

## Development & Testing
- **Test Framework**: PHPUnit (`phpunit/phpunit: ^10.0`) defined in `composer.json` and configured via `phpunit.xml`.
- **Package Management**: Composer (PSR-4 autoloading and test scripts only).
- **Environment**: Laragon, Apache, Nginx, or Docker with standard LAMP/LEMP configurations.
