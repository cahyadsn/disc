# PHP Code Style Guide: DISC Personality Test

This style guide establishes coding standards and conventions for the DISC Personality Test codebase, ensuring high maintainability, security, and performance targeting **PHP 8.3 as the minimum requirement**.

---

## 1. Core Principles & Portability

- **PHP 8.3 Minimum Requirement**:
  - **Strict Types**: Always declare `declare(strict_types=1);` at the top of all PHP files immediately after the opening tag.
  - **Type Declarations**: Use strict scalar, compound, and union type declarations for all function/method parameters, return types, and class properties.
  - **Typed Class Constants**: Utilize typed class constants (e.g. `public const array DIMENSIONS = ['D', 'I', 'S', 'C'];`, `public const int DEFAULT_VAL = 15;`) to prevent unexpected type coercion.
  - **Exception Handling**: Use `Throwable` in top-level or generic catch blocks to reliably intercept both engine-level errors and runtime exceptions without leaking credentials.
  - **Modern Language Features**:
    - Use `str_starts_with()`, `str_ends_with()`, and `str_contains()` instead of legacy `strpos()` checks.
    - Use PHP 8.3 `json_validate()` to verify JSON integrity before decoding cached data.
    - Set dynamic timezone fallbacks (e.g., `date_default_timezone_set('UTC')`) when initializing to avoid runtime CLI warnings.
- **Cross-Platform Test Execution**:
  - Ensure tests run cleanly across Windows and POSIX systems.
  - Dynamically skip POSIX-specific behaviors (such as `chmod` file permission checks) when running on Windows platforms (`DIRECTORY_SEPARATOR === '\\'`).

---

## 2. File Headers & Metadata

All modified or new PHP script files must include standardized header blocks:

```php
/**
 * [File Purpose / Description]
 *
 * @author    [Author Name]
 * @copyright [Copyright Info]
 * @license   [License]
 * @version   [Version]
 *
 * UPDATED DATE: yyyy-mm-dd hh:ii:ss
 */
```

- **Date Formatting**: `UPDATED DATE` must strictly follow the `yyyy-mm-dd hh:ii:ss` 24-hour timestamp format.

---

## 3. Formatting & PSR Standards

- **Indentation**: 4 spaces per indentation level (no tabs).
- **Line Endings**: Standard UNIX `LF` (`\n`).
- **PHP Tags**: Always use `<?php` long tags. Never use short open tags `<?`.
- **Closing Tags**: Omit closing `?>` tags at the end of pure PHP files to prevent unexpected whitespace output.
- **Braces**:
  - Classes and functions: Opening brace on a new line.
  - Control structures (`if`, `foreach`, `while`): Opening brace on the same line.
- **Namespaces & Autoloading**:
  - Follow PSR-4 standards under the `Disc\` root namespace mapping to `src/`.
  - Use PascalCase for class names and camelCase for method/property names.

---

## 4. Security & Error Handling

- **Credential Redaction**:
  - Never expose database credentials, hostnames, or raw DSN strings in logs, exceptions, or user-facing messages.
  - Wrap database connection failures in sanitized exceptions or friendly fallback responses.
- **SQL Injection Prevention**:
  - Always use prepared statements with parameter binding for dynamic SQL inputs.
  - Validate statement preparation (`$stmt !== false`) before binding parameters or calling `execute()`.
- **Database Query Optimization**:
  - Favor single round-trip queries (such as `UNION ALL` fallbacks) over multiple sequential database round-trips.
  - Avoid MySQL 8.0+ specific syntax (such as Common Table Expressions / CTEs) in core portable queries; use derived subqueries for compatibility across MySQL 5.7+ and MariaDB.

---

## 5. Testing Standards

- **Framework**: PHPUnit (`phpunit/phpunit: ^10.0`).
- **Execution**: Run tests via `composer test`.
- **Test Organization**:
  - Place unit and integration test cases in `tests/`.
  - Mock environment variables or database configurations cleanly without polluting global state.
