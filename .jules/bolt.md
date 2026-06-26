2024-06-16 - [Persistent Database Connections]
**Learning:** Using persistent connections in `mysqli` by simply prepending `p:` to the `$dbhost` can significantly reduce overhead by pooling and reusing database connections, yielding a ~30% improvement in connection times in microbenchmarks.
**Action:** Default to using persistent connections (`p:`) for `mysqli` to limit the overhead per request in PHP scripts that establish new database connections for every request.
