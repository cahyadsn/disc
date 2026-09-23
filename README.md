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
  * `db.php`: Centralized helper that encapsulates database configuration loading with safe error handling across page endpoints.
  * `autoload_env.php`: A native, zero-dependency environment variables loader that parses and applies configuration variables from `.env`.
  * `headers.php`: Central security headers configuration ensuring custom protection rules are applied uniformly across PHP page endpoints.
* **`/db`**: Contains database schema and seed data files (`disc.sql`).
* **`/cache`**: Local cache directory containing pre-compiled HTML templates (`html_cache.html`) and parsed environment variable cache files (`env_*.php`), protected against direct HTTP access via `.htaccess`.
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

* **Core Engine**: PHP (supports version 5.6 and above)
  * **Native Environment Variables Loader**: Automatically loads credentials from a `.env` file via `conf/autoload_env.php` using pure native PHP.
  * **Lazy-Loading Database Connection**: Database connections are deferred and only established on cache misses.
  * **Persistent Database Pooling**: Configured with persistent connections (`p:`) to minimize TCP handshake and connection authentication overhead.
* **Database & Query Layer**: MySQL / MariaDB
  * **Single Round-trip Fallbacks**: Optimized data retrieval utilizing SQL `UNION ALL` to resolve pattern records and application fallbacks in a single database query.
  * **Prepared Statements**: Secure parameter binding utilizing mysqli prepared statements.
* **Caching & Performance Optimization**:
  * **HTML File Caching**: Pre-compiles the heavily nested rendering loop output to an HTML cache file (`html_cache.html`) in the local `cache/` directory (protected via `.htaccess` to prevent direct HTTP access), yielding a ~98% speedup.
  * **Environment Variables Caching**: Caches parsed `.env` variables into compiled PHP cache files (`cache/env_*.php`) to eliminate file parsing overhead on repeated requests.
  * **Result Profile Caching**: Caches resolved DISC profile results (`cache/result_*.php`) to eliminate database queries and pattern resolution overhead for identical personality test submissions.
  * **Filesystem Call Reductions**: Uses `is_readable()` to perform cache-hit checks in one step, bypassing redundant `file_exists()` checks.
  * **Single-Pass Value Aggregation**: Direct mutation of the result array in `result.php` avoids intermediate array allocations and the difference aggregation loop, yielding a ~45% speedup.
  * **Static Key Optimization**: Removed redundant `htmlspecialchars` escaping on hardcoded, static array keys in the `result.php` rendering loop to eliminate unnecessary function call overhead.
  * **Loop & Memory Optimizations**: Minimized array allocations, inlined closures, and avoided nested calculations inside loops.
* **Security & Hardening**:
  * **Dotfile & Sensitive File Protection**: Root `.htaccess` configuration blocks direct HTTP access to dotfiles and sensitive metadata (such as `.env` and `.git`).
  * **Session Cookie Security**: Configures `session.cookie_secure` and `session.cookie_httponly` flags before session start to protect session tokens against network interception and client-side script access.
  * **CSRF Protection**: Form submissions on `index.php` are protected against Cross-Site Request Forgery (CSRF) via session-backed, cryptographically secure random tokens validated with `hash_equals()` in `result.php`.
  * **HTTP Security Headers & HSTS**: Implements strict protection headers including `Strict-Transport-Security: max-age=31536000; includeSubDomains`, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, and a fine-tuned `Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com;` to prevent clickjacking, MIME sniffing, protocol downgrades, and cross-site scripting (XSS).
  * **XSS Defenses**: Sanitized and escaped HTML output using `htmlspecialchars` with UTF-8 encoding.
  * **Input Size & Array Limit Validation**: Form input arrays (`$_POST['m']` and `$_POST['l']`) are strictly sliced to a maximum of 28 elements via `array_slice` in `result.php`, mitigating unbounded array iteration and denial-of-service (DoS) payloads.
  * **Sensitive Data Redaction & Stack Trace Preservation**: Safe exception handling prevents database password and credential leaks in debug logs and user interfaces while preserving original exception stack traces for effective debugging.
* **Frontend & Presentation**:
  * **Glassmorphic UI Design**: Refactored to a sleek, modern visual aesthetic featuring background blurs (`backdrop-filter`), translucent panels, glowing border/shadow effects, and gradient backdrops.
  * **Typography**: Clean visual styling built on the `Plus Jakarta Sans` Google Font.
  * **Tactile Custom Controls**: Standard radio inputs are styled into custom selection buttons that glow emerald-green for "Most" choices and rose-red for "Least" choices.
  * **Responsive Dashboard Grid**: Layout cards and lists adapt fluidly to screen dimensions, providing a highly premium experience on both desktop and mobile.
* **Testing & CI/CD**:
  * **PHPUnit Framework**: Unit test suite covering SQL injection mitigations, XSS checks, caching mechanics, exception context preservation, database connection failures, and invalid POST fallbacks.
  * **Standalone Test Suites & Error Path Coverage**: Comprehensive standalone regression tests covering edge cases such as missing/empty database results, filesystem permission and `mkdir` failures via custom stream wrappers, CSRF verification, and security headers.
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
### Recent Updates (2026-09-23)
- **Security & Input Validation**:
  - Mitigated unbounded array iteration vulnerabilities by slicing input arrays (`$_POST['m']` and `$_POST['l']`) to a maximum of 28 elements using `array_slice` in `result.php`, preventing potential DoS attacks.
- **Testing & Quality Assurance**:
  - Added `tests/test_result_array_limit.php` to verify input size truncation and boundary enforcement.
  - Added `tests/test_result_cache_write_failure.php` to verify error logging when writing to result cache files fails.
  - Refactored working directory comment in `tests/test_cache_mkdir_failure.php` for clarity.

### Recent Updates (2026-09-17)
- **Code Health & Refactoring**:
  - Extracted and centralized duplicated database configuration loading logic from `index.php` and `result.php` into a dedicated helper `conf/db.php`.
  - Refactored test environment isolation check in `conf/autoload_env.php` using an isolated closure.
- **Error Handling & Diagnostics**:
  - Preserved original exception context and stack traces in `conf/config.php` during database connection failures while ensuring sensitive credentials remain redacted.

### Recent Updates (2026-09-16)
- **Performance & Optimization**:
  - Implemented file-based caching for resolved personality profile results in `result.php` (`cache/result_*.php`), eliminating database queries and fallback lookups on identical test submissions.
- **Code Health & Error Handling**:
  - Added explicit error handling and error logging (`error_log`) when cache directory creation (`mkdir`) and file write operations (`file_put_contents`) fail in `conf/autoload_env.php`.
- **Testing & Quality Assurance**:
  - Added `tests/test_autoload_env_cache_write_failure.php` to verify error logging when `.env` cache file writing fails.
  - Added `tests/test_autoload_env_mkdir_failure.php` using a custom PHP stream wrapper (`MkdirFailingWrapper`) to test error logging during directory creation failure in `conf/autoload_env.php`.

### Recent Updates (2026-09-14)
- **Testing & Quality Assurance**:
  - Added `tests/test_cache_mkdir_failure.php` using a custom PHP stream wrapper (`MkdirFailingWrapper`) to verify error logging and graceful failure paths when `mkdir` fails during cache directory initialization in `index.php`.
  - Added `tests/test_result_empty_result.php` to verify graceful fallback handling and error messaging when database queries return no rows or missing data in `result.php`.

### Recent Updates (2026-09-08)
- **Performance & Optimization**:
  - Implemented compilation caching for parsed environment variables in `conf/autoload_env.php` (`cache/env_*.php`), eliminating redundant file reads and string parsing on subsequent requests.
  - Inlined the `$render_cell` closure in the inner table generation loop of `index.php`, eliminating function call overhead during HTML rendering.

*For earlier updates and the complete changelog history, please refer to [change_log.md](change_log.md).*

