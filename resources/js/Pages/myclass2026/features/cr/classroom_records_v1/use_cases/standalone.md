# Use Case — Standalone Page (Interactive Context)

Goal:
- Teacher opens Classroom Records v1 directly and selects context manually.

## Expected Inputs

External options the container should accept:
- `mode: "interactive"`
- `context`: optional initial values
- `options`: optional lists passed from outside (avoid extra API fetch)
- `apiBase`: default `/api/cr`

## Example Props (shape)

```json
{
  "mode": "interactive",
  "context": {
    "classroom_id": null,
    "subject_id": null,
    "date": "2026-03-15",
    "day_number": null,
    "period_number": null,
    "period_code": null
  },
  "options": {
    "classrooms": [{ "id": 1, "name": "A1" }],
    "subjects": [{ "id": 10, "name": "Math" }]
  },
  "apiBase": "/api/cr"
}
```

## UI Behavior

- SessionContextBar allows selecting classroom + subject + date + day/period.
- System derives `period_code` as `Y{year_id}-S#-W#-D#-P#`.
- When context becomes valid → calls `POST {apiBase}/init-session`.
