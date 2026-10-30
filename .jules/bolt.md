## 2026-08-09 - Hash map lookups vs array_count_values
**Learning:** In PHP, dynamically building associative arrays for frequency counting (`array_count_values` or dynamic `$map[$v] = ($map[$v] ?? 0) + 1`) from user input incurs overhead for key allocation and null coalescing checks.
**Action:** When the set of expected keys is known and small (like 'D', 'I', 'S', 'C'), pre-initialize the associative array to zero (`['D'=>0]`) and use strict input validation (`is_scalar($v) && isset($map[$v])`) to directly increment values. This is not only ~20-25% faster but also safer against input type tampering (e.g., nested arrays).

## 2026-08-28 - String Interpolation vs Array Appends
**Learning:** In PHP rendering loops, consolidating multiple array appending operations (e.g., multiple `$html[] = ...`) into a single string interpolation append per loop iteration (`$html[] = "{$a}{$b}{$c}";`) provides measurable performance gains (roughly 15% speedup in our benchmarks). This is because it reduces array resizing overhead and string concatenation (`.`) mechanics, especially when combined with avoiding multiple `$html[] = ...` calls.
**Action:** When unrolling loops or dealing with large string building in hot paths, prefer consolidating chunks into a single interpolated string rather than pushing many small strings onto an array.

## 2026-10-16 - Inline closures in tight loops
**Learning:** In PHP, creating and calling closures (anonymous functions) repeatedly in tight loops adds significant execution overhead (about 3x slower in our benchmark) compared to having the logic directly inline.
**Action:** When a closure's logic is only used in a couple of places within the same function and is inside a loop, inline the logic directly instead of invoking the closure to improve execution speed.

## 2026-10-21 - Avoid @ operator even if slightly faster
**Learning:** Using the `@` error suppression operator (e.g. `@array_count_values`) in PHP is considered a dangerous anti-pattern because it suppresses all warnings and errors, which makes debugging incredibly difficult if unexpected types are encountered. Even if it measures slightly faster than a manual loop with `isset` and `is_scalar` checks, the loss in safety and maintainability is not worth the micro-optimization.
**Action:** When counting frequencies in mixed-type arrays, avoid `@array_count_values`. Instead, pre-initialize the result array with expected keys and use a `foreach` loop with strict type checking (`is_scalar`) and existence checking (`isset($map[$v])`) to directly mutate values.
