# Undo Changes - Reverting Commits and Modifications

## ✅ When to Use This Guide
- You made a mistake in your last commit
- You want to discard uncommitted changes
- You need to undo a push to GitHub
- You committed the wrong files
- You want to go back to a previous version

## 📋 Prerequisites
- You're in your project directory
- You have Git initialized

---

## 🎯 Choose Your Scenario

### Scenario 1: Undo Uncommitted Changes (Not Yet Committed)

**Situation:** You modified files but haven't committed yet.

#### Discard ALL Uncommitted Changes
```bash
# WARNING: This deletes all your changes!
git reset --hard HEAD

# Verify
git status
```

#### Discard Changes in Specific File
```bash
# Restore one file to last commit
git restore resources/js/Pages/my_table_mnger/reward_sys/reward_sys.vue

# Or use checkout (older method)
git checkout -- resources/js/Pages/my_table_mnger/reward_sys/reward_sys.vue
```

#### Unstage Files (Keep Changes, Remove from Staging)
```bash
# Unstage all files
git reset

# Unstage specific file
git restore --staged resources/js/Pages/my_table_mnger/reward_sys/reward_sys.vue
```

---

### Scenario 2: Undo Last Commit (Not Pushed Yet)

**Situation:** You committed but haven't pushed to GitHub.

#### Option A: Undo Commit, Keep Changes
```bash
# Undo commit but keep your changes
git reset --soft HEAD~1

# Your files are still modified and staged
git status
```

**Use when:** You want to recommit with a different message or add more files.

#### Option B: Undo Commit, Unstage Changes
```bash
# Undo commit and unstage, but keep changes
git reset HEAD~1

# Your files are modified but not staged
git status
```

**Use when:** You want to review changes before recommitting.

#### Option C: Undo Commit, Delete Changes
```bash
# WARNING: This deletes your changes!
git reset --hard HEAD~1

# Everything is gone
git status
```

**Use when:** You want to completely remove the commit and changes.

---

### Scenario 3: Undo Multiple Commits (Not Pushed)

```bash
# Undo last 3 commits, keep changes
git reset --soft HEAD~3

# Undo last 3 commits, delete changes
git reset --hard HEAD~3

# Go back to specific commit
git reset --hard abc1234
```

**Find commit hash:**
```bash
git log --oneline -10
```

---

### Scenario 4: Undo After Pushing to GitHub

**Situation:** You already pushed to GitHub and want to undo.

#### Option A: Revert (Creates New Commit) - SAFE
```bash
# Revert last commit (creates new commit)
git revert HEAD

# Push the revert
git push origin main
```

**Advantages:**
- ✅ Safe - doesn't rewrite history
- ✅ Good for shared repositories
- ✅ Keeps history intact

#### Option B: Reset & Force Push - DANGEROUS
```bash
# Undo last commit locally
git reset --hard HEAD~1

# Force push to GitHub
git push --force origin main
```

**⚠️ WARNING:**
- 🚨 Rewrites history
- 🚨 Can cause problems for others
- 🚨 Only use if you're the only one working on the project

---

### Scenario 5: Undo Specific File from Last Commit

**Situation:** You want to remove one file from the last commit.

```bash
# Remove file from last commit
git reset HEAD~1 resources/js/Pages/my_table_mnger/reward_sys/reward_sys.vue

# Or restore file to previous version
git restore --source=HEAD~1 resources/js/Pages/my_table_mnger/reward_sys/reward_sys.vue

# Recommit without that file
git add .
git commit -m "Updated message"
```

---

### Scenario 6: Change Last Commit Message

**Situation:** You want to fix the commit message.

#### If Not Pushed Yet
```bash
# Change last commit message
git commit --amend -m "New commit message"
```

#### If Already Pushed
```bash
# Change message
git commit --amend -m "New commit message"

# Force push
git push --force origin main
```

---

### Scenario 7: Add Files to Last Commit

**Situation:** You forgot to include files in your last commit.

```bash
# Add the forgotten files
git add forgotten-file.vue

# Amend the last commit
git commit --amend --no-edit

# If already pushed, force push
git push --force origin main
```

---

## 📝 Complete Examples

### Example 1: Oops, Wrong Commit Message
```bash
# You committed with wrong message
git commit -m "fix bug"

# Change it
git commit --amend -m "Fixed reward system timestamp bug"

# If not pushed yet, just push normally
git push origin main

# If already pushed, force push
git push --force origin main
```

---

### Example 2: Committed Too Early
```bash
# You committed but forgot some files
git commit -m "Added reward system"

# Add forgotten files
git add forgotten-component.vue

# Amend the commit
git commit --amend --no-edit

# Push (force if already pushed)
git push origin main
```

---

### Example 3: Want to Start Over
```bash
# Discard all changes and go back to last commit
git reset --hard HEAD

# Or go back to last pushed version
git reset --hard origin/main

# Verify
git status
```

---

### Example 4: Undo Last 3 Commits
```bash
# See commits
git log --oneline -5

# Undo last 3, keep changes
git reset --soft HEAD~3

# Review changes
git status

# Recommit properly
git add .
git commit -m "Proper commit message"
git push origin main
```

---

## ⚠️ Understanding Reset Options

| Command | Commit | Staging | Working Directory |
|---------|--------|---------|-------------------|
| `--soft` | ✅ Undo | ✅ Keep | ✅ Keep |
| `--mixed` (default) | ✅ Undo | ❌ Unstage | ✅ Keep |
| `--hard` | ✅ Undo | ❌ Delete | ❌ Delete |

---

## 🚨 Emergency Recovery

### Recover Deleted Commits
```bash
# See all actions (including deleted commits)
git reflog

# Find the commit you want
# Example output:
# abc1234 HEAD@{0}: reset: moving to HEAD~1
# def5678 HEAD@{1}: commit: My deleted commit

# Restore it
git reset --hard def5678
```

---

### Recover Deleted Files
```bash
# If you deleted a file and want it back
git restore deleted-file.vue

# If you committed the deletion
git restore --source=HEAD~1 deleted-file.vue
```

---

## 💡 Best Practices

### 1. Check Before Undoing
```bash
# Always check what you're about to undo
git log --oneline -5
git status
git diff
```

### 2. Use Soft Reset First
```bash
# Safer - keeps your changes
git reset --soft HEAD~1

# Review
git status

# If you're sure, use hard reset
git reset --hard HEAD~1
```

### 3. Avoid Force Push on Shared Repos
If others are working on the project, use `git revert` instead of `git reset --hard` + force push.

### 4. Create a Backup Branch
```bash
# Before major undo operations
git branch backup-before-reset

# Now you can safely experiment
git reset --hard HEAD~5

# If something goes wrong
git reset --hard backup-before-reset
```

---

## 🎯 Quick Decision Guide

| Situation | Command | Safe? |
|-----------|---------|-------|
| Discard uncommitted changes | `git reset --hard HEAD` | ⚠️ |
| Undo last commit, keep changes | `git reset --soft HEAD~1` | ✅ |
| Undo last commit, delete changes | `git reset --hard HEAD~1` | ⚠️ |
| Undo pushed commit | `git revert HEAD` | ✅ |
| Change commit message | `git commit --amend` | ✅ |
| Add to last commit | `git commit --amend --no-edit` | ✅ |

---

## 🔍 Checking What Will Be Undone

```bash
# See what's in the last commit
git show HEAD

# See what's in the commit before last
git show HEAD~1

# See difference between commits
git diff HEAD~1 HEAD

# See commit history
git log --oneline --graph -10
```

---

## 🔗 Related Guides
- [Checking Status](./03-checking-status.md) - Before undoing
- [Stash Changes](./09-stash-changes.md) - Temporary save
- [Daily Commit & Push](./01-daily-commit-push.md) - Proper workflow
- [Force Push](./08-force-push.md) - When you need to force push

---

**Last Updated:** 2025-12-12
