# Checking Status - Understanding Your Repository State

## ✅ When to Use This Guide
- Before committing changes
- Before pushing to GitHub
- To see what files you've modified
- To understand where you are in the Git workflow
- When something seems wrong

## 📋 Prerequisites
- You're in your project directory
- Git is initialized

---

## 🔧 Essential Status Commands

### Command 1: Basic Status Check
```bash
git status
```

**What it shows:**
- Current branch
- Files you've modified
- Files staged for commit
- Files not tracked by Git
- Whether you're ahead/behind remote

---

## 📊 Understanding Git Status Output

### Scenario 1: Clean Working Tree
```bash
$ git status
On branch main
Your branch is up to date with 'origin/main'.

nothing to commit, working tree clean
```

**Meaning:** ✅ No changes, everything is committed and pushed.

**What to do:** Nothing! You're all set.

---

### Scenario 2: Modified Files (Not Staged)
```bash
$ git status
On branch main
Your branch is up to date with 'origin/main'.

Changes not staged for commit:
  (use "git add <file>..." to update what will be committed)
  (use "git restore <file>..." to discard changes in working directory)
        modified:   resources/js/Pages/my_table_mnger/reward_sys/reward_sys.vue
        modified:   resources/js/Pages/my_table_mnger/reward_sys/reward_sys_comp/StudentGrouping.vue

no changes added to commit (use "git add" and/or "git commit -a")
```

**Meaning:** ⚠️ You have changes, but they're not staged for commit.

**What to do:**
```bash
# Add all changes
git add .

# Or add specific files
git add resources/js/Pages/my_table_mnger/reward_sys/reward_sys.vue
```

---

### Scenario 3: Staged Files (Ready to Commit)
```bash
$ git status
On branch main
Your branch is up to date with 'origin/main'.

Changes to be committed:
  (use "git restore --staged <file>..." to unstage)
        modified:   resources/js/Pages/my_table_mnger/reward_sys/reward_sys.vue
        modified:   resources/js/Pages/my_table_mnger/reward_sys/reward_sys_comp/StudentGrouping.vue
```

**Meaning:** ✅ Files are staged and ready to commit.

**What to do:**
```bash
# Commit the changes
git commit -m "Your descriptive message"
```

---

### Scenario 4: Ahead of Remote
```bash
$ git status
On branch main
Your branch is ahead of 'origin/main' by 2 commits.
  (use "git push" to publish your local commits)

nothing to commit, working tree clean
```

**Meaning:** ⚠️ You have committed changes that aren't pushed to GitHub.

**What to do:**
```bash
# Push to GitHub
git push origin main
```

---

### Scenario 5: Behind Remote
```bash
$ git status
On branch main
Your branch is behind 'origin/main' by 3 commits, and can be fast-forwarded.
  (use "git pull" to update your local branch)

nothing to commit, working tree clean
```

**Meaning:** ⚠️ GitHub has changes you don't have locally.

**What to do:**
```bash
# Pull latest changes
git pull origin main
```

---

### Scenario 6: Diverged Branches
```bash
$ git status
On branch main
Your branch and 'origin/main' have diverged,
and have 2 and 3 different commits each, respectively.
  (use "git pull" to merge the remote branch into yours)
```

**Meaning:** 🚨 You and remote both have different changes.

**What to do:**
```bash
# Pull and merge
git pull origin main

# You might get merge conflicts - see: 04-merge-conflicts.md
```

---

### Scenario 7: Untracked Files
```bash
$ git status
On branch main
Untracked files:
  (use "git add <file>..." to include in what will be committed)
        new-component.vue
        test-file.js

nothing added to commit but untracked files present (use "git add" to track)
```

**Meaning:** ℹ️ New files that Git doesn't know about yet.

**What to do:**
```bash
# Add new files
git add new-component.vue test-file.js

# Or add all
git add .
```

---

## 🔍 Advanced Status Commands

### See Detailed Changes
```bash
# See what changed in files (not staged)
git diff

# See what changed in staged files
git diff --staged

# See changes in a specific file
git diff resources/js/Pages/my_table_mnger/reward_sys/reward_sys.vue
```

---

### See Commit History
```bash
# See recent commits
git log --oneline -10

# See commits not pushed yet
git log origin/main..HEAD

# See who changed what
git log --oneline --graph --all
```

**Example output:**
```
5dad0e7 (HEAD -> main) Merge remote changes, keeping local improvements
a1b2c3d Improving reward system with timestamp
d4e5f6g Added student grouping component
```

---

### See File Status Summary
```bash
# Short status (compact view)
git status -s
```

**Example output:**
```
M  resources/js/Pages/my_table_mnger/reward_sys/reward_sys.vue
M  resources/js/Pages/my_table_mnger/reward_sys/reward_sys_comp/StudentGrouping.vue
?? new-file.vue
```

**Legend:**
- `M` = Modified
- `A` = Added
- `D` = Deleted
- `??` = Untracked
- `MM` = Modified and staged

---

### Check Remote Connection
```bash
# See remote repository
git remote -v

# Check if you can connect
git fetch --dry-run
```

---

## 📝 Complete Status Check Workflow

Here's a complete workflow to understand your repository state:

```bash
# 1. Navigate to project
cd /Users/ahmedmosaad/Herd/myclass2026-main

# 2. Check basic status
git status

# 3. See what changed
git diff

# 4. See recent commits
git log --oneline -5

# 5. Check remote connection
git remote -v

# 6. Check if remote has updates
git fetch
git status
```

---

## 🎯 Quick Decision Guide

Based on `git status` output, here's what to do:

| Status Message | What It Means | What To Do |
|----------------|---------------|------------|
| "nothing to commit, working tree clean" | ✅ All good | Nothing |
| "Changes not staged" | ⚠️ Modified files | `git add .` |
| "Changes to be committed" | ✅ Ready to commit | `git commit -m "message"` |
| "Your branch is ahead" | ⚠️ Need to push | `git push origin main` |
| "Your branch is behind" | ⚠️ Need to pull | `git pull origin main` |
| "have diverged" | 🚨 Conflicts possible | `git pull origin main` |
| "Untracked files" | ℹ️ New files | `git add .` |

---

## 💡 Best Practices

### 1. Check Status Often
```bash
# Before starting work
git status

# Before committing
git status

# Before pushing
git status

# After pulling
git status
```

### 2. Use Short Status for Quick Checks
```bash
# Quick view
git status -s

# Full view when you need details
git status
```

### 3. Understand Before Acting
Don't just run commands blindly. Read the status output and understand what it's telling you.

---

## ⚠️ Common Warnings Explained

### "detached HEAD state"
```bash
# You're not on a branch
# Get back to main:
git checkout main
```

### "fatal: not a git repository"
```bash
# You're not in the right directory
cd /Users/ahmedmosaad/Herd/myclass2026-main

# Or git isn't initialized
git init
```

### "No commits yet"
```bash
# Repository is empty
# Make your first commit:
git add .
git commit -m "Initial commit"
```

---

## 🔗 Related Guides
- [Daily Commit & Push](./01-daily-commit-push.md) - After checking status
- [Merge Conflicts](./04-merge-conflicts.md) - If branches diverged
- [Undo Changes](./05-undo-changes.md) - If you want to discard changes
- [Pull Latest Changes](./06-pull-latest-changes.md) - If behind remote

---

**Last Updated:** 2025-12-12
