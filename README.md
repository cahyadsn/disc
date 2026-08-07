# DISC
DISC Personality Test in PHP language based on DISC Classic. Build on PHP language and MySQL/MariaDB database server (dummy data included, real data excluded* )  
![screenshot](https://github.com/cahyadsn/disc/blob/master/screenshot/home.png?raw=true)

Demo link : 
- [https://psycho.cahyadsn.com/disc](https://psycho.cahyadsn.com/disc) [ver 0.6 English version]
- [https://psycho.cahyadsn.com/disc/index.es.php](https://psycho.cahyadsn.com/disc/index.es.php) [ver 0.7 Spanish version] 

[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![GitHub last commit](https://img.shields.io/github/last-commit/google/skia.svg?style=flat)]()
[![Donate](https://img.shields.io/badge/$-support-ff69b4.svg?style=flat)](https://paypal.me/cahyadwiana)  

)* I can't provide the real data as on demo for this github repo since this data is proprietary (see Reference section) 

## Installation
1. download 'disc_master.zip' file
2. extract and copy all files to document root folder on your webserver (or other folder that you want)
3. create new database named 'test'
4. import 'db/disc.sql' to the 'test' database
5. Copy `.env.example` to `.env` and customize your database credentials (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`). The application will automatically load these credentials using a native PHP loader.
6. Try accessing http://localhost (or the target folder configured in step 2). Enjoy!

## Project Structure

The project directory has been reorganized to keep configuration and test layers clean:

* **`/conf`**: Directory holding central configuration and autoload helper:
  * `config.php`: Central database configuration setup with lazy-loading connection pooling.
  * `autoload_env.php`: A native, zero-dependency environment variables loader that parses and applies configuration variables from `.env`.
  * `headers.php`: Central security headers configuration ensuring custom protection rules are applied uniformly across PHP page endpoints.
* **`/db`**: Contains database schema and seed data files (`disc.sql`).
* **`/tests`**: Contains the PHPUnit and standalone test suites covering security, SQL injection mitigations, cache handlers, and platform-specific tests.
* **`/assets`**: Frontend styles, fonts, and assets.

## Running Tests

To verify code changes, security fixes, and database behaviors, run the test suite:

1. Install development dependencies:
   ```bash
   composer install
   ```
2. Execute the test suite:
   ```bash
   composer test
   ```
   *Note: Standalone test scripts can also be run individually (e.g., `php tests/test_config.php`). For a full list of tests, see the [tests/README.md](file:///D:/laragon/repo/dev/disc/tests/README.md) file.*

## Reference
+ [**DiSC Classic Paper Profile** -  DiSC® 2800 Series Personal Profile System®](https://www.discprofile.com/products/disc-classic/)

![screenshot](https://github.com/cahyadsn/disc/blob/master/screenshot/result.png?raw=true)  

## Technology Stack & Architecture

This project is built using a lightweight and highly optimized architecture designed for performance, security, and portability:

* **Core Engine**: PHP (supports version 8.x and above)
  * **Native Environment Variables Loader**: Automatically loads credentials from a `.env` file via `conf/autoload_env.php` using pure native PHP.
  * **Lazy-Loading Database Connection**: Database connections are deferred and only established on cache misses.
  * **Persistent Database Pooling**: Configured with persistent connections (`p:`) to minimize TCP handshake and connection authentication overhead.
* **Database & Query Layer**: MySQL / MariaDB
  * **Single Round-trip Fallbacks**: Optimized data retrieval utilizing SQL `UNION ALL` to resolve pattern records and application fallbacks in a single database query.
  * **Prepared Statements**: Secure parameter binding utilizing mysqli prepared statements.
* **Caching & Performance Optimization**:
  * **HTML File Caching**: Pre-compiles the heavily nested rendering loop output to an HTML cache file (`html_cache.html`) in the system temporary directory (to prevent direct HTTP access), yielding a ~98% speedup.
  * **Filesystem Call Reductions**: Uses `is_readable()` to perform cache-hit checks in one step, bypassing redundant `file_exists()` checks.
  * **Loop & Memory Optimizations**: Minimized array allocations and nested calculations inside loops.
* **Security & Hardening**:
  * **HTTP Security Headers**: Implements custom protection rules such as `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, and a restrictive `Content-Security-Policy: default-src 'self';` to defend against clickjacking, MIME sniffing, and cross-site scripting (XSS).
  * **XSS Defenses**: Sanitized and escaped HTML output using `htmlspecialchars` with UTF-8 encoding.
  * **Sensitive Data Redaction**: Safe exception handling prevents database password and credential leaks in debug logs and user interfaces by stripping underlying exception context during connection failures.
* **Frontend & Presentation**:
  * **Glassmorphic UI Design**: Refactored to a sleek, modern visual aesthetic featuring background blurs (`backdrop-filter`), translucent panels, glowing border/shadow effects, and gradient backdrops.
  * **Typography**: Clean visual styling built on the `Plus Jakarta Sans` Google Font.
  * **Tactile Custom Controls**: Standard radio inputs are styled into custom selection buttons that glow emerald-green for "Most" choices and rose-red for "Least" choices.
  * **Responsive Dashboard Grid**: Layout cards and lists adapt fluidly to screen dimensions, providing a highly premium experience on both desktop and mobile.
* **Testing & CI/CD**:
  * **PHPUnit Framework**: Unit test suite covering SQL injection mitigations, XSS checks, caching mechanics, exception context preservation, database connection failures, and invalid POST fallbacks.
  * **Cross-platform Compatibility**: Test scripts dynamically adapt to and run reliably on both Unix/Linux and Windows environments.

## Donation
- untuk donasi via transfer
    - Bank BCA Digital (Blu) (501) 000 576 776 186
    - Bank Jago (542) 5003 5796 1022
    - Bank Sinarmas (153) 005 462 4719
    - Bank Syariah Indonesia (BSI) 821-342-5550
- untuk donasi via PayPal [https://paypal.me/cahyadwiana]
- untuk donasi via QRIS CAHYADSN ID1022183125288 :

![screenshot](https://github.com/cahyadsn/wilayah/blob/master/docs/qr_code.cahyadsn.png?raw=true 'Donasi via QRIS CAHYADSN')

## Contact
+ facebook : [https://m.facebook.com/cahya.dsn](https://m.facebook.com/cahya.dsn)
+ email : [cahyadsn@gmail.com](mailto:cahyadsn@gmail.com)
+ demo site    : [https://psycho.cahyadsn.com/disc](https://psycho.cahyadsn.com/disc) [en] [https://psycho.cahyadsn.com/disc/index.es](https://psycho.cahyadsn.com/disc/index.es) [es-dev]
+ source code  : [https://github.com/cahyadsn/disc](https://github.com/cahyadsn/disc)

## Contributor
+ Aleksandar Urosevic
+ Ikbal Qodi
+ Lucas Giovanny

## Changelog
### Recent Updates (2026-08-07)
- **Testing & Quality**:
  - Added test coverage for `loadEnv` edge cases in `conf/autoload_env.php` via `tests/test_autoload_env.php`.

### Recent Updates (2026-08-06)
- **Database & Query Optimization**:
  - Refactored the SQL query in `result.php` to use a Common Table Expression (CTE) to reduce subquery overhead and optimize performance.
- **Testing & Quality**:
  - Introduced `tests/test_autoload_env.php` to comprehensively test the environment variable loader `loadEnv()`, checking comments, spaces, quotes, empty values, missing/unreadable files, and env var overwriting prevention.

### Recent Updates (2026-08-05)
- **Configuration & Quality**:
  - Corrected `DB_PASS` validation logic in `conf/config.php` to safely check `$_ENV['DB_PASS']` and prevent PHP warnings when the database password is empty or unset.

### Recent Updates (2026-08-04)
- **Security & Error Handling**:
  - Wrapped database configuration inclusion in a try-catch block within `index.php` and `result.php` to prevent uncaught database initialization exception exposure.
  - Added checks to verify if `$db` is defined before executing queries or preparing statements.

### Recent Updates (2026-08-02)
- **Security & Refactoring**:
  - Extracted duplicated security headers from `index.php` and `result.php` into a central configuration file `conf/headers.php`, and updated tests to verify its presence.
- **Code Quality & Health**:
  - Fixed an unreachable condition in the database password (`DB_PASS`) check in `conf/config.php` when the password is set to an empty string.
  - Refactored the `$result` array structure in `result.php` to avoid unused nested keys (`most`, `least`, `change`), simplifying it to store only the calculated scalar difference.

### Recent Updates (2026-07-31)
- **Performance & Optimizations**:
  - Hoisted loop invariants out of the inner rendering loop in `index.php` to prevent redundant computations and CSS class string interpolations on every iteration.

### Recent Updates (2026-07-30)
- **Code Standardization**:
  - Standardized file headers with comprehensive MIT License blocks, authorship metadata, and description fields across core scripts (`conf/autoload_env.php`, `conf/config.php`, `index.php`, and `result.php`).
- **Testing & Quality Assurance**:
  - Refactored directory change (`chdir`) inline comments in the unreadable cache test suite to explicitly describe execution context configuration, satisfying static analysis and preventing false positives from TODO scanners.

### Recent Updates (2026-07-28)
- **UI & Presentation**:
  - Implemented distinct background tone colors (`q-odd` and `q-even`) for odd/even numbered question groups (groups of 4 terms) to improve readability and visual separation.
- **Testing & Security Coverage**:
  - Added robust test coverage for invalid personality dimension values in POST inputs to verify database integration safety.
  - Added security test suite verifying XSS escaping rules on `result.php` template rendering.
  - Added test coverage targeting database execution failures to verify transaction resilience and graceful fallbacks.

### Recent Updates (2026-07-26)
- **Security & Caching**:
  - Moved `html_cache.html` out of the web root into the system temporary directory (`sys_get_temp_dir()`) to prevent direct public HTTP access.
  - Implemented `Content-Security-Policy: default-src 'self';` header on both `index.php` and `result.php`.
  - Redacted the database connection exception context (stripped internal PHP exception chains) to prevent accidental database credential leaks in debug logs.
  - Removed redundant `file_exists()` checks before checking `is_readable()` when resolving HTML cache hits.

### Recent Updates (2026-07-25)
- **Configuration & Security**:
  - Implemented a native PHP `.env` loader (`conf/autoload_env.php`) to keep credential configurations clean and isolated.
  - Created `.env.example` template.
  - Updated `.gitignore` to prevent database configuration credentials from being checked into source control.
  - Relocated configuration and variable loading files to the `/conf` directory (`conf/config.php` and `conf/autoload_env.php`).

### Recent Updates (2026-07-24)
- **UI Refactoring & Styling**:
  - Restructured layout templates with modern Glassmorphic panel designs, fluid containers, and dynamic radial background glows.
  - Implemented tactile custom-styled radio buttons that dynamically glow green (Most) and rose (Least) when checked.
  - Re-skinned results into a responsive grid dashboard matching premium modern design frameworks.
  - Automatically cleared file caches to seamlessly render the modernized structure.

### Recent Updates (2026-07-21)
- **Database & Query Optimization**:
  - Fixed a prepared statement parameter count mismatch by binding all 8 parameters for the single `UNION ALL` query in `result.php`.
  - Eliminated redundant secondary execution calls to implement a true single round-trip database fallback flow.
- **Testing**:
  - Updated test cases to assert single statement execution.
  - Improved test compatibility on Windows environments by bypassing POSIX-specific chmod file permissions tests.

### Recent Updates (2026-07-19 to 2026-07-20)
- **Database & Query Optimization**:
  - Refactored the result pattern query to use a `UNION ALL` fallback in `result.php`.
- **Code Cleanup & Refactoring**:
  - Refactored database property rendering in `result.php`.
  - Removed unnecessary '#' aspect processing in `result.php`.
  - Guarded against statement `prepare()` failure in `result.php`.
- **Security & Error Handling**:
  - Fixed discarded exception context in `config.php`.
- **Header Information**:
  - Updated `UPDATED DATE` header in `index.php` and `result.php` to `2026-07-20 08:04:50` using the `yyyy-mm-dd hh:ii:ss` format.
### Recent Updates (2026-07-18 23:06:34)
- **Performance & Optimizations (Bolt)**:
  - Optimized array allocations and iterations in the rendering and view rendering loops.
  - Streamlined array traversals and refactored loops in `result.php`.
  - Removed redundant mathematical calculations inside nested loops.
- **Security Enhancements**:
  - Prevented potential database password leaks in database exceptions.
  - Added essential HTTP security headers to `index.php` and `result.php`.
- **Code Quality & Health**:
  - Cleaned up database connection error suppression in `config.php`.
  - Fixed type mismatches and object fallback logic in `result.php`.
- **Testing**:
  - Added new test suites covering query failures, unreadable cache file fallback, and HTML cache write failures.
  - Updated test documentation.

