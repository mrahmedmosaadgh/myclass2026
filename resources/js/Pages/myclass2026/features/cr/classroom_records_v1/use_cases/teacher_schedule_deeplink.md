# Use Case — Teacher Schedule Deep Link (Readonly Context)

Goal:
- Teacher clicks a schedule cell and opens Classroom Records v1 for that exact slot.
- No manual selection needed.

## Deep Link URL

Example:

`/classroom-manager?classroom_id=5&subject_id=12&teacher_id=7&date=2026-03-15&day_number=2&period_number=3&period_code=Y2026-S1-W12-D2-P3`

## Required Context

```json
{
  "mode": "readonly",
  "context": {
    "classroom_id": 5,
    "subject_id": 12,
    "teacher_id": 7,
    "date": "2026-03-15",
    "day_number": 2,
    "period_number": 3,
    "period_code": "Y2026-S1-W12-D2-P3"
  }
}
```

## UI Behavior

- SessionContextBar is locked.
- Page immediately calls `POST /api/cr/init-session` with the context.
- Records render instantly (cards).
