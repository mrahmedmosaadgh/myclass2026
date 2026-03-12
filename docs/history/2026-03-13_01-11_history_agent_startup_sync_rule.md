# 2026-03-13 01:11 | Enforce Git Sync on Agent Startup

## What was done:
- Updated the `.agent/rules/GEMINI.md` core rulefile.
- Added a `MANDATORY STARTUP CHECK` to the `Enforcement Protocol` section.
- The AI is now explicitly instructed to run `git fetch` and `git status` at the very beginning of every session (before writing code or plans) to detect if the local branch is behind the remote repository.
- If the branch is behind, the AI is instructed to stop and tell the user to run `git pull --rebase --autostash` (or ask for permission to run it).

## What still needs to be done:
- None. This rule applies immediately to all future AI interactions in this workspace.
