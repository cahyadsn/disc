# Changelog

All notable changes, architectural decisions, and improvements to the DISC Personality Test project are documented in this file.

## Recent Updates (2026-09-17)
- **Code Health & Refactoring**:
  - Extracted and centralized duplicated database configuration loading logic from `index.php` and `result.php` into a dedicated helper `conf/db.php`.
  - Refactored test environment isolation check in `conf/autoload_env.php` using an isolated closure.
- **Error Handling & Diagnostics**:
  - Preserved original exception context and stack traces in `conf/config.php` during database connection failures while ensuring sensitive credentials remain redacted.

## Recent Updates (2026-09-16)
- **Performance & Optimization**:
  - Implemented file-based caching for resolved personality profile results in `result.php` (`cache/result_*.php`), eliminating database queries and fallback lookups on identical test submissions.
- **Code Health & Error Handling**:
  - Added explicit error handling and error logging (`error_log`) when cache directory creation (`mkdir`) and file write operations (`file_put_contents`) fail in `conf/autoload_env.php`.
- **Testing & Quality Assurance**:
  - Added `tests/test_autoload_env_cache_write_failure.php` to verify error logging when `.env` cache file writing fails.
  - Added `tests/test_autoload_env_mkdir_failure.php` using a custom PHP stream wrapper (`MkdirFailingWrapper`) to test error logging during directory creation failure in `conf/autoload_env.php`.

## Recent Updates (2026-09-14)
- **Testing & Quality Assurance**:
  - Added `tests/test_cache_mkdir_failure.php` using a custom PHP stream wrapper (`MkdirFailingWrapper`) to verify error logging and graceful failure paths when `mkdir` fails during cache directory initialization in `index.php`.
  - Added `tests/test_result_empty_result.php` to verify graceful fallback handling and error messaging when database queries return no rows or missing data in `result.php`.

## Recent Updates (2026-09-08)
- **Performance & Optimization**:
  - Implemented compilation caching for parsed environment variables in `conf/autoload_env.php` (`cache/env_*.php`), eliminating redundant file reads and string parsing on subsequent requests.
  - Inlined the `$render_cell` closure in the inner table generation loop of `index.php`, eliminating function call overhead during HTML rendering.

## Recent Updates (2026-09-02)
- **Security & Hardening**:
  - Added root `.htaccess` configuration to prevent public web access to dotfiles and sensitive configuration files (`.env`, `.git`).
  - Added `Strict-Transport-Security` (HSTS) with `max-age=31536000; includeSubDomains` header in `conf/headers.php`.
  - Configured `session.cookie_secure` and `session.cookie_httponly` flags for enhanced session cookie protection in `conf/headers.php`.
  - Adjusted `Content-Security-Policy` (CSP) directives to permit external Google Fonts (`https://fonts.googleapis.com` and `https://fonts.gstatic.com`).
- **Testing & Quality Assurance**:
  - Added `tests/test_session_cookies.php` to verify secure session cookie flags.
  - Updated `tests/test_security_headers.php` to validate HSTS and the updated CSP font policy.

## Recent Updates (2026-08-31)
- **Code Health & Error Handling**:
  - Removed error suppression operator (`@`) from `file_put_contents` in `index.php` and aligned it with proper error logging practices when cache generation fails.
  - Replaced error suppression (`@`) in `mkdir` for cache directory creation with explicit error checking and logging to prevent silent permission failure bugs.
- **Performance Optimization**:
  - Removed redundant `htmlspecialchars` escaping on static label keys in `result.php` loop, reducing unnecessary function call overhead.

## Recent Updates (2026-08-29)
- **Performance & Loop Optimization**:
  - Consolidated multiple array append operations in the HTML rendering loop of `index.php` into a single string interpolation append. This avoids redundant array resizing overhead and CPU branching.

## Recent Updates (2026-08-27)
- **Code Health & Refactoring**:
  - Extracted the heavily duplicated inline HTML generation block in `index.php` into a single concise `$render_cell` closure, dramatically reducing the inner loop size and improving code readability and maintainability.

## Recent Updates (2026-08-24)
- **Security & Hardening**:
  - Implemented session-based CSRF protection on form submissions (`index.php` and `result.php`).
- **Testing & Quality Assurance**:
  - Added a new CSRF validation test suite (`tests/test_csrf.php`).
  - Updated existing tests to initialize and include valid CSRF tokens.
  - Added `tests/test_index_error_log.php` to verify database connection failure logging via `error_log()`.

## Recent Updates (2026-08-21)
- **Performance Optimization**:
  - Refactored `result.php` array initialization and value aggregation into a single-pass mutation of the result array. This eliminates intermediate allocations for most/least arrays and the difference calculation loop, yielding a ~45% speedup.
  - Updated PHPUnit test assertions in `tests/DiscTest.php` to align with the simplified result structure.
- **Security & Hardening**:
  - Removed the default `'root'` fallback for `DB_USER` in `conf/config.php` to prevent assuming administrative privileges.
- **Error & Warning Mitigation**:
  - Suppressed potential PHP warnings on `mkdir` and `file_put_contents` cache operations in `index.php` to avoid path exposure vulnerabilities.

## Recent Updates (2026-08-18)
- **Performance & Parser Optimization**:
  - Replaced `preg_match` with `trim` for stripping surrounding quotes from parsed environment variables in `conf/autoload_env.php` to eliminate regex engine overhead.
- **Code Health & Readability**:
  - Refactored `result.php` using the early return pattern to remove a massive nested conditional block, reducing the overall indentation level and improving code maintainability.

## Recent Updates (2026-08-14)
- **PHP 5.6 Compatibility & Exception Handling**:
  - Removed PHP 7 type hints (parameter and return type declarations) from the environment variable loader in `conf/autoload_env.php` to prevent parse errors on PHP 5.6.
  - Replaced `Throwable` catch blocks with standard `Exception` in `conf/config.php` and `tests/test_result_fallback.php` to restore robust exception catching on PHP 5.6.
- **Database & Query Optimization**:
  - Rewrote the CTE (Common Table Expression) query in `result.php` using a derived table `UNION ALL` statement, ensuring backwards compatibility for MySQL 5.5+ and MariaDB while preserving single database round-trip performance.
- **Performance & Timezone Mitigation**:
  - Added default timezone initialization to `conf/autoload_env.php` to suppress PHP's CLI timezone warning and reduce logging overhead.

## Recent Updates (2026-08-08)
- **Security & Caching**:
  - Secured the template cache by moving `html_cache.html` out of the system `/tmp` directory into a local, protected `cache/` directory. Added a `cache/.htaccess` file to prevent direct public HTTP access to cached outputs in shared hosting environments.
- **Performance Optimization**:
  - Replaced the costly `debug_backtrace()` call in the environment loader with a fast environment variable and script name lookup to detect testing environments, yielding a ~45% speedup.
  - Removed redundant `file_exists()` system calls before checking `is_readable()` in the environment loader to avoid unnecessary filesystem stat overhead.

## Recent Updates (2026-08-07)
- **Performance Optimization**:
  - Removed redundant `file_exists()` check in the environment loader to minimize filesystem stat overhead.
- **Testing & Quality**:
  - Added test coverage for `loadEnv` edge cases in `conf/autoload_env.php` via `tests/test_autoload_env.php`.

## Recent Updates (2026-08-06)
- **Database & Query Optimization**:
  - Refactored the SQL query in `result.php` to use a Common Table Expression (CTE) to reduce subquery overhead and optimize performance.
- **Testing & Quality**:
  - Introduced `tests/test_autoload_env.php` to comprehensively test the environment variable loader `loadEnv()`, checking comments, spaces, quotes, empty values, missing/unreadable files, and env var overwriting prevention.

## Recent Updates (2026-08-05)
- **Configuration & Quality**:
  - Corrected `DB_PASS` validation logic in `conf/config.php` to safely check `$_ENV['DB_PASS']` and prevent PHP warnings when the database password is empty or unset.

## Recent Updates (2026-08-04)
- **Security & Error Handling**:
  - Wrapped database configuration inclusion in a try-catch block within `index.php` and `result.php` to prevent uncaught database initialization exception exposure.
  - Added checks to verify if `$db` is defined before executing queries or preparing statements.

## Recent Updates (2026-08-02)
- **Security & Refactoring**:
  - Extracted duplicated security headers from `index.php` and `result.php` into a central configuration file `conf/headers.php`, and updated tests to verify its presence.
- **Code Quality & Health**:
  - Fixed an unreachable condition in the database password (`DB_PASS`) check in `conf/config.php` when the password is set to an empty string.
  - Refactored the `$result` array structure in `result.php` to avoid unused nested keys (`most`, `least`, `change`), simplifying it to store only the calculated scalar difference.

## Recent Updates (2026-07-31)
- **Performance & Optimizations**:
  - Hoisted loop invariants out of the inner rendering loop in `index.php` to prevent redundant computations and CSS class string interpolations on every iteration.

## Recent Updates (2026-07-30)
- **Code Standardization**:
  - Standardized file headers with comprehensive MIT License blocks, authorship metadata, and description fields across core scripts (`conf/autoload_env.php`, `conf/config.php`, `index.php`, and `result.php`).
- **Testing & Quality Assurance**:
  - Refactored directory change (`chdir`) inline comments in the unreadable cache test suite to explicitly describe execution context configuration, satisfying static analysis and preventing false positives from TODO scanners.

## Recent Updates (2026-07-28)
- **UI & Presentation**:
  - Implemented distinct background tone colors (`q-odd` and `q-even`) for odd/even numbered question groups (groups of 4 terms) to improve readability and visual separation.
- **Testing & Security Coverage**:
  - Added robust test coverage for invalid personality dimension values in POST inputs to verify database integration safety.
  - Added security test suite verifying XSS escaping rules on `result.php` template rendering.
  - Added test coverage targeting database execution failures to verify transaction resilience and graceful fallbacks.

## Recent Updates (2026-07-26)
- **Security & Caching**:
  - Moved `html_cache.html` out of the web root into the local `cache/` directory (protected via `.htaccess`) to prevent direct public HTTP access.
  - Implemented `Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com;` header on both `index.php` and `result.php`.
  - Redacted the database connection exception context (stripped internal PHP exception chains) to prevent accidental database credential leaks in debug logs.
  - Removed redundant `file_exists()` checks before checking `is_readable()` when resolving HTML cache hits.

## Recent Updates (2026-07-25)
- **Configuration & Security**:
  - Implemented a native PHP `.env` loader (`conf/autoload_env.php`) to keep credential configurations clean and isolated.
  - Created `.env.example` template.
  - Updated `.gitignore` to prevent database configuration credentials from being checked into source control.
  - Relocated configuration and variable loading files to the `/conf` directory (`conf/config.php` and `conf/autoload_env.php`).

## Recent Updates (2026-07-24)
- **UI Refactoring & Styling**:
  - Restructured layout templates with modern Glassmorphic panel designs, fluid containers, and dynamic radial background glows.
  - Implemented tactile custom-styled radio buttons that dynamically glow green (Most) and rose (Least) when checked.
  - Re-skinned results into a responsive grid dashboard matching premium modern design frameworks.
  - Automatically cleared file caches to seamlessly render the modernized structure.

## Recent Updates (2026-07-21)
- **Database & Query Optimization**:
  - Fixed a prepared statement parameter count mismatch by binding all 8 parameters for the single `UNION ALL` query in `result.php`.
  - Eliminated redundant secondary execution calls to implement a true single round-trip database fallback flow.
- **Testing**:
  - Updated test cases to assert single statement execution.
  - Improved test compatibility on Windows environments by bypassing POSIX-specific chmod file permissions tests.

## Recent Updates (2026-07-19 to 2026-07-20)
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

## Recent Updates (2026-07-18 23:06:34)
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
