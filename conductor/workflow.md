# Development Workflow: DISC Personality Test

## TDD Policy & Strictness
- **Strict TDD Enforcement**: Tests must be written and observed failing before implementation code is added.
- **Test Command**: Run `composer test` (PHPUnit) to execute the test suite across unit and integration tests.
- **Platform Verification**: Ensure tests pass portably across Windows and Linux environments (e.g. handle dynamic skips for OS-specific file permission checks).

## Commit Strategy & Conventions
- **Conventional Commits**: Every commit must follow standard conventional commit prefixing:
  - `feat:` New features or user-facing additions
  - `fix:` Bug fixes and corrections
  - `docs:` Documentation updates, docblocks, or architectural records
  - `refactor:` Code improvements without behavioral changes
  - `test:` Adding or updating test suites
  - `perf:` Performance optimizations
  - `chore:` Dependency or build adjustments
- **Message Clarity**: Subject line should be concise and descriptive.

## Code Review Policy
- **Required for Non-Trivial Changes**: All architectural adjustments, database query optimizations, security updates, and breaking refactors require review before merging.
- **Context Preservation**: Exceptions must never leak credentials or database connection details.

## Verification Checkpoints
- **Phase Completion Checkpoints**: Verify implementation, tests, and documentation after each track phase.
- **Verification Protocol**:
  1. Execute `composer test` and ensure all tests pass cleanly.
  2. Confirm compatibility constraints (PHP 8.3 syntax, no type hints, `Exception` catches).
  3. Validate file headers follow `yyyy-mm-dd hh:ii:ss` format when modified.

## Task Lifecycle
1. **Context & Spec**: Define track requirements in `spec.md` and breakdown in `plan.md`.
2. **Red Phase**: Write failing unit or integration tests defining expected behavior.
3. **Green Phase**: Write the minimal code to satisfy the tests.
4. **Refactor Phase**: Clean up and optimize while preserving passing tests.
5. **Phase Checkpoint**: Run full test suite, verify against acceptance criteria, and update `setup_state.json` / `plan.md`.
