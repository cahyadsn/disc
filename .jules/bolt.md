## 2024-06-25 - [Missing DB Indexes in PHP application]
**Learning:** In traditional PHP/MySQL applications, SQL scripts used for initial schema definitions (like `db/disc.sql`) often lack necessary indexes for fields frequently queried or used in complex JOINs (like `graph`, `dimension`, `value` in the `results` table, and `d`, `i`, `s`, `c` in `pattern_map`).
**Action:** When optimizing traditional LAMP stack apps, inspect the SQL schema definition files for missing `PRIMARY KEY` and `KEY` definitions on columns heavily used in `WHERE` and `JOIN` clauses to prevent O(N) full table scans.
