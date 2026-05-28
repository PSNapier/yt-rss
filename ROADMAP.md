# Roadmap

<!-- Next task number: [002] -->

## [001] Banked Kaaring EXAMPLE ENTRY

**Status:** `in-progress`
**Priority:** high
**Depends On:** none

### Goal

Allow players to "bank" externally earned KP onto a character via item redemption or admin action.

### Scope

- Max 300 banked KP per character
- Integration with existing item redeem flow or dedicated admin action (TBD)

### Technical Notes

- Needs `[004]` submission/KP pipeline in place first
- May require a `banked_kp` column on `characters` to track cap separately from earned KP

### Acceptance Criteria

- [ ] Spec finalized for how banking is triggered (item vs. admin action)
- [ ] 300 KP cap enforced per character
- [ ] Banked KP reflected in `kaaring_count` and level progression

---
