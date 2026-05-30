2024-05-17 - [Unconditional DB Connection]
**Learning:** Establishing a database connection on every request, even when a cache hit occurs, incurs unnecessary network/latency overhead. In legacy procedural scripts, requires like `require_once 'db.php'` at the top of the file can negate the benefits of caching.
**Action:** Move expensive initializations (like database connections) inside the exact execution block where a cache miss requires them.
