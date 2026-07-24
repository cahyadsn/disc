## 2024-07-24 - Avoid micro-optimizing small fixed array iterations
**Learning:** Refactoring a 4-iteration `foreach` loop into direct variable assignments yields an unmeasurable performance gain and violates the requirement for measurable impact.
**Action:** Avoid micro-optimizations on infinitesimally small operations. Focus on high-frequency loops (like the main HTML generation loop) where consolidating array appends provides a tangible speedup.
