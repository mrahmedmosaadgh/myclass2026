# Git Sync Issue: Root Cause Analysis & Prevention

**Date:** 2026-03-25  
**Context:** During the push of Presentation V4 features, multiple Git failures occurred (Merge Conflicts + RPC 408 Timeout).

## The Problems

### 1. Massive Merge Conflicts (100+ files)
- **Reason:** A `git pull --rebase` was attempted on a branch (`main3-clean`) that had diverged significantly from the remote. Rebase tries to re-apply every local commit one by one on top of the remote. Because the remote had many new files (some of which were binary `.DS_Store` or auto-generated), Git flagged them as "add/add" conflicts for almost every file in the project.
- **Why it felt "broken":** During a rebase, your local directory structure can temporarily "disappear" or move to a temporary state while Git processes the commits. This made it look like the `v4/` folder was missing.

### 2. RPC failed; HTTP 408 (Push Timeout)
- **Reason:** The commit was very large (17MB+ pack file) containing many new components and media. The default Git buffer size and the network connection timed out (HTTP 408) before the upload finished.

## The Solutions (Applied)

1. **Aborted Rebase:** Stopped the complex rebase and used `git merge` instead. Merging is safer for large diverged branches because it creates a single "unification" point rather than rewriting history.
2. **Standard Merge:** Successfully unified `main3-clean` with `origin/main3-clean`.
3. **Buffer Increase:** Ran `git config http.postBuffer 524288000` (500MB) to allow large file uploads without timing out.

## Prevention Strategy (For Future)

1. **Sync Early, Sync Often:** Before starting ANY new feature, run `git pull` promptly.
2. **Merge over Rebase for Large Changes:** If you haven't synced in a while, use `git pull --no-rebase` (standard merge). It’s much easier to resolve one merge conflict than 100 individual rebase steps.
3. **Configure Buffers:** Keep `http.postBuffer` high on this machine to support Large File Storage (LFS) and complex Vue builds.
4. **Ignore .DS_Store:** Ensure `.gitignore` is properly configured to ignore macOS system files like `.DS_Store` which are a frequent source of "add/add" conflicts.
