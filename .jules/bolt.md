## 2024-06-25 - [Missing DB Indexes in PHP application]
**Learning:** In traditional PHP/MySQL applications, SQL scripts used for initial schema definitions (like `db/disc.sql`) often lack necessary indexes for fields frequently queried or used in complex JOINs (like `graph`, `dimension`, `value` in the `results` table, and `d`, `i`, `s`, `c` in `pattern_map`).
**Action:** When optimizing traditional LAMP stack apps, inspect the SQL schema definition files for missing `PRIMARY KEY` and `KEY` definitions on columns heavily used in `WHERE` and `JOIN` clauses to prevent O(N) full table scans.
## 2024-05-24 - Database Optimization: Avoiding Cross-Joined Derived Tables
**Learning:** Using multiple derived tables with a cross-join (Cartesian product) to perform constant-time lookups forces MySQL to create temporary tables, which is highly inefficient for CPU and memory, especially without proper indexing on derived tables.
**Action:** Replace cross-joined derived tables with uncorrelated direct subqueries in the `WHERE` clause (e.g., `WHERE col = (SELECT val FROM table LIMIT 1)`) to allow the database to use primary key indexes effectively and avoid temporary table creation entirely.
