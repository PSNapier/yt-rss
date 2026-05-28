---
name: roadmap
description: Manage active items in ROADMAP.md — update statuses, check off acceptance criteria, edit scope/notes, or add new items. Use when the user says /roadmap, asks about roadmap items, mentions starting/finishing a task tracked in the roadmap, or references item IDs like [002], [005].
---

# Roadmap

Active project work lives in `ROADMAP.md` at the repo root. Completed items move to `ROADMAP_DONE.md` (use the `/roadmap-done` skill for that).

Always read `ROADMAP.md` before answering questions about priorities, current work, or dependencies.

## Status Vocabulary

Use exactly these values, lowercase, backtick-quoted:

| Status | Meaning |
|---|---|
| `next` | Queued, not started |
| `in-progress` | Actively being worked on |
| `freezer` | Deferred, spec or dependency missing |
| `done` | All acceptance criteria checked |

Do not invent statuses. If blocked mid-flight, keep `in-progress` and note the blocker in `Technical Notes`.

## When to Update

- **Starting work** → set `Status` to `in-progress`
- **Acceptance criterion met** → check the box `- [x]`
- **All criteria checked** → set `Status` to `done`, then prompt user to run `/roadmap-done` to archive
- **Scope / Technical Notes / Acceptance Criteria change** → edit in the same commit as the code change
- **New item** → use the template below, assign next available `[NNN]` ID (zero-padded, increment from highest in either `ROADMAP.md` or `ROADMAP_DONE.md`)

## Item Template

```markdown
## [NNN] Title

**Status:** `next`
**Priority:** low | medium | high
**Depends On:** none | [NNN], [NNN]

### Goal

One-paragraph outcome.

### Scope

-   Bulleted scope items
-   What is explicitly NOT in scope

### Technical Notes

-   Implementation details, commands, data, file paths

### Acceptance Criteria

-   [ ] Checkable, testable outcome
-   [ ] Another outcome
```

## Formatting Rules

- Items separated by `---` on its own line
- Bullet style: `-   ` (dash + 3 spaces) to match existing file
- IDs: `[NNN]` three-digit zero-padded (`[002]`, `[015]`)
- One blank line between heading and `**Status:**` block
- Keep ordering: `Status` → `Priority` → `Depends On`

## Common Operations

**Start an item:**
1. Read `ROADMAP.md`, find `[NNN]`
2. Change `**Status:** `next`` → `**Status:** `in-progress``
3. Stage with related code changes

**Complete a criterion:**
1. Locate the `- [ ]` line
2. Replace with `- [x]`
3. If all boxes checked, prompt: "All criteria met for [NNN]. Run /roadmap-done to archive?"

**Add a blocker note:**
- Append to `### Technical Notes`: `**Blocked:** <reason> (<date>)`
- Keep `Status` as `in-progress` (or move to `freezer` if blocker is long-term)
