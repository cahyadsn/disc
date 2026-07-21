# Gemini Learnings and Actions

This document tracks learnings, architectural decisions, and actions taken by Gemini while developing and maintaining the DISC Personality Test project.

---


## 2026-07-21 - Database & Query Optimization: Multi-Parameter prepared statements and Test Suite Windows Portability
**Learning:** SQL queries that feature fallback mechanisms via `UNION ALL` can require different numbers of parameters than what simple application-level logic expects. Failing to bind all parameters (8 in this query structure) leads to database driver execution errors on real systems. Additionally, `chmod` file permission tests do not behave portably on Windows platforms and should be dynamically skipped.
**Action:** Bind all 8 parameters for the single `UNION ALL` statement in `result.php` and remove secondary execution blocks to finalize the single round-trip optimization. Refactor related fallback tests and add platform checks to skip POSIX-specific tests on Windows.

## 2026-07-20 - Header Standardization: Updated Date Formatting
**Learning:** File headers containing metadata (like `UPDATED DATE`) can easily become out-of-sync or use inconsistent formats if not standardized. Enforcing a strict date-time format (such as `yyyy-mm-dd hh:ii:ss`) across all file headers ensures traceabilty and consistency.
**Action:** Update the file header blocks of key files like `index.php` and `result.php` to use the standard `yyyy-mm-dd hh:ii:ss` format.

## 2026-07-19 - Database & Query Optimization: UNION ALL Fallback
**Learning:** When retrieving records that have a structured fallback configuration in case of database misses, executing secondary fallback queries adds connection and query latency. Implementing a SQL-based `UNION ALL` query with a default/fallback row can resolve the value in a single database round-trip.
**Action:** Refactor result pattern queries in `result.php` to leverage database-level fallbacks using structured query logic instead of application-level secondary queries.

## 2026-07-19 - Security & Error Handling: Preserving Exception Context Safely
**Learning:** Suppressing database connection errors or throwing generic exceptions without preserving underlying context makes system failures difficult to diagnose. However, raw exception messages must not leak credentials.
**Action:** Refactor database exception handling in `db.php` to clean up connection error suppression while ensuring connection secrets are redacted from any logged or displayed error messages.

## 2026-07-19 - Code Quality: Statement Preparation Robustness
**Learning:** Assuming database `prepare()` statements always succeed without verification can cause fatal errors when queries fail due to connection issues or schema drift.
**Action:** Add checks to verify statement objects before binding parameters or executing, gracefully falling back to default results if preparation fails.
