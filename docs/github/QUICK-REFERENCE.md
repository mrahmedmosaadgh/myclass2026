# Git & GitHub Quick Reference Cheat Sheet

## 🚀 Most Common Commands

### Daily Workflow
```bash
# Check status
git status

# Add all changes
git add .

# Commit with message
git commit -m "Your descriptive message"

# Push to GitHub
git push origin main

# Pull from GitHub
git pull origin main
```

---

## 📋 Command Categories

### 1. Setup & Configuration
```bash
# Set your name
git config --global user.name "Ahmed Mosaad"

# Set your email
git config --global user.email "your-email@example.com"

# Initialize repository
git init

# Add remote
git remote add origin https://github.com/mrahmedmosaadgh/myclass2026.git

# Check configuration
git config --list
```

---

### 2. Checking Status
```bash
# See status
git status

# Short status
git status -s

# See changes
git diff

# See staged changes
git diff --staged

# See commit history
git log --oneline -10

# See graphical history
git log --oneline --graph --all
```

---

### 3. Adding & Committing
```bash
# Add all files
git add .

# Add specific file
git add filename.vue

# Add all .vue files
git add *.vue

# Commit
git commit -m "Message"

# Add and commit in one
git commit -am "Message"

# Amend last commit
git commit --amend -m "New message"
```

---

### 4. Pushing & Pulling
```bash
# Push to main
git push origin main

# Push new branch
git push -u origin branch-name

# Pull from main
git pull origin main

# Fetch only (no merge)
git fetch origin

# Force push (careful!)
git push --force-with-lease origin main
```

---

### 5. Branches
```bash
# List branches
git branch

# Create branch
git branch branch-name

# Create and switch
git checkout -b branch-name

# Switch branch
git checkout branch-name

# Delete branch
git branch -d branch-name

# Merge branch
git merge branch-name
```

---

### 6. Undoing Changes
```bash
# Discard uncommitted changes
git restore filename.vue

# Discard all changes
git reset --hard HEAD

# Unstage file
git restore --staged filename.vue

# Undo last commit (keep changes)
git reset --soft HEAD~1

# Undo last commit (delete changes)
git reset --hard HEAD~1

# Revert pushed commit
git revert HEAD
```

---

### 7. Stashing
```bash
# Stash changes
git stash

# Stash with message
git stash save "Message"

# List stashes
git stash list

# Apply latest stash
git stash pop

# Apply specific stash
git stash apply stash@{0}

# Delete stash
git stash drop
```

---

### 8. Remote Operations
```bash
# View remotes
git remote -v

# Add remote
git remote add origin URL

# Change remote URL
git remote set-url origin URL

# Remove remote
git remote remove origin

# Fetch all branches
git fetch --all
```

---

### 9. Merge Conflicts
```bash
# Keep your version
git checkout --ours filename.vue

# Keep their version
git checkout --theirs filename.vue

# Keep your version for all
git checkout --ours .

# Abort merge
git merge --abort

# After resolving
git add .
git commit -m "Resolved conflicts"
```

---

### 10. Information & Inspection
```bash
# Show commit details
git show HEAD

# Show file at specific commit
git show abc1234:filename.vue

# See who changed what
git blame filename.vue

# See file history
git log --follow filename.vue

# Compare branches
git diff main..feature-branch

# See remote branches
git branch -r
```

---

## 🎯 Common Scenarios

### Scenario: Daily Work
```bash
cd /Users/ahmedmosaad/Herd/myclass2026-main
git pull origin main
# ... make changes ...
git add .
git commit -m "Improved reward system"
git push origin main
```

---

### Scenario: Merge Conflicts
```bash
git pull origin main
# CONFLICT appears
git checkout --ours .
git add .
git commit -m "Merged keeping local changes"
git push origin main
```

---

### Scenario: Undo Last Commit
```bash
git reset --soft HEAD~1
git status
# Make changes
git add .
git commit -m "Better commit message"
git push origin main
```

---

### Scenario: Start New Feature
```bash
git checkout main
git pull origin main
git checkout -b feature/new-feature
# ... work on feature ...
git add .
git commit -m "Added new feature"
git push origin feature/new-feature
```

---

### Scenario: Save Work Temporarily
```bash
git stash save "WIP: Reward system"
git pull origin main
git stash pop
```

---

## 🚨 Emergency Commands

### Undo Everything (Dangerous!)
```bash
git reset --hard HEAD
git clean -fd
```

### Go Back to Remote Version
```bash
git fetch origin
git reset --hard origin/main
```

### Recover Deleted Commit
```bash
git reflog
git reset --hard abc1234
```

### Start Fresh
```bash
rm -rf .git
git init
git remote add origin URL
git fetch origin
git reset --hard origin/main
```

---

## 📊 Git Status Meanings

| Symbol | Meaning |
|--------|---------|
| `M` | Modified |
| `A` | Added |
| `D` | Deleted |
| `R` | Renamed |
| `C` | Copied |
| `U` | Unmerged |
| `??` | Untracked |

---

## 🎨 Useful Aliases

Add to `~/.gitconfig`:

```bash
[alias]
    st = status
    co = checkout
    br = branch
    ci = commit
    unstage = reset HEAD --
    last = log -1 HEAD
    visual = log --oneline --graph --all
    undo = reset --soft HEAD~1
```

Usage:
```bash
git st          # instead of git status
git co main     # instead of git checkout main
git visual      # see pretty graph
```

---

## 🔍 Troubleshooting Quick Fixes

| Problem | Solution |
|---------|----------|
| Not a git repository | `git init` |
| Remote already exists | `git remote set-url origin URL` |
| Permission denied | Switch to HTTPS URL |
| Merge conflicts | `git checkout --ours .` |
| Diverged branches | `git pull origin main` |
| Detached HEAD | `git checkout main` |
| Can't push | `git pull origin main` first |

---

## 📁 Project-Specific Commands

### Your Project Path
```bash
cd /Users/ahmedmosaad/Herd/myclass2026-main
```

### Your Repository
```bash
https://github.com/mrahmedmosaadgh/myclass2026.git
```

### Quick Update
```bash
cd /Users/ahmedmosaad/Herd/myclass2026-main && git add . && git commit -m "Update" && git push origin main
```

---

## 💡 Best Practices

✅ **DO:**
- Commit often with clear messages
- Pull before you push
- Check status before committing
- Use branches for features
- Write descriptive commit messages

❌ **DON'T:**
- Force push to shared branches
- Commit sensitive data (.env files)
- Make huge commits
- Use vague commit messages
- Work directly on main for big changes

---

## 🔗 Full Guides

For detailed explanations, see:
- [Daily Commit & Push](./01-daily-commit-push.md)
- [First Time Setup](./02-first-time-setup.md)
- [Checking Status](./03-checking-status.md)
- [Merge Conflicts](./04-merge-conflicts.md)
- [Undo Changes](./05-undo-changes.md)
- [Pull Latest Changes](./06-pull-latest-changes.md)
- [Branch Management](./07-branch-management.md)
- [Force Push](./08-force-push.md)
- [Stash Changes](./09-stash-changes.md)
- [Repository Issues](./10-repository-issues.md)

---

**Print this page and keep it handy!**

**Last Updated:** 2025-12-12
