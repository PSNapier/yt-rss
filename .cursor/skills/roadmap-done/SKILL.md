---
name: roadmap-done
description: Close out a roadmap item at end of session — reconcile ROADMAP.md against work actually completed (check boxes, set status to done), then archive the item to ROADMAP_DONE.md. Use when the user says /roadmap-done, finishes a session and wants to wrap up an item, or asks to archive [NNN].
---

# Roadmap Done

End-of-session closeout: update `ROADMAP.md` to reflect completed work, then move the item to `ROADMAP_DONE.md`.

## When to Use

- User says `/roadmap-done`
- End of a session where work on `[NNN]` is complete but `ROADMAP.md` not yet reconciled
- User asks to archive `[NNN]`

## Assumption

`ROADMAP.md` may be **out of date**. Status may still say `in-progress`. Acceptance boxes may still be `- [ ]` even though the work happened. Do not refuse to proceed — reconcile first.

## Procedure

### 1. Identify the item

- Ask user which `[NNN]` if ambiguous
- Read the item block from `ROADMAP.md`

### 2. Reconcile against session work

Review the conversation history / session changes and map them to each acceptance criterion. For each `- [ ]`:

- **Clearly completed** → propose checking it
- **Partially done / unclear** → flag to user, do not check
- **Not done** → leave unchecked

Present the proposed updates to the user as a list:

```
[002] Admin Character Review Panel
Proposed updates:
  - [x] Admin can sort table by missing data type        (done — commit abc123)
  - [x] Modal opens with DA description data on row click (done — commit def456)
  - [ ] Admin can approve/reject/edit... (NOT done — modal still read-only)

Status: in-progress → ??? (not all criteria met; cannot archive yet)
```

### 3. Decide path

- **All criteria check off** → proceed to step 4 (update + archive)
- **Some criteria remain** → stop. Update `ROADMAP.md` with the verified checkboxes, leave `Status` as `in-progress`, tell user item cannot be archived yet. Done.
- **User overrides** (e.g. "criterion no longer relevant, drop it") → edit the criterion line per user instruction, then re-evaluate

### 4. Apply updates to ROADMAP.md

- Check the agreed boxes (`- [ ]` → `- [x]`)
- Set `**Status:** `in-progress`` → `**Status:** `done``
- Apply any agreed scope/criteria edits

### 5. Move item to ROADMAP_DONE.md

- Cut the full item block from `ROADMAP.md` (heading through trailing `---`)
- Remove any orphan `---` left behind in `ROADMAP.md`
- Insert at the **top** of `ROADMAP_DONE.md` (newest-first ordering), directly after the `# Roadmap Done` header, followed by a trailing `---` separator
- Preserve content verbatim during the move

### 6. Confirm

Report: `Archived [NNN] Title → ROADMAP_DONE.md` with summary of any criteria/scope edits made.

## Safeguards

- Never mark a criterion `- [x]` without evidence in session work or explicit user confirmation
- Never set `Status: done` if any `- [ ]` remains
- Never delete from `ROADMAP.md` before confirming the append to `ROADMAP_DONE.md` succeeded
- Never renumber `[NNN]` IDs
- If the item is already in `ROADMAP_DONE.md` (duplicate), abort and tell user
