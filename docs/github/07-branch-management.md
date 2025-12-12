# Branch Management - Working with Git Branches

## ✅ When to Use This Guide
- You want to work on a new feature without affecting main code
- You need to experiment with changes
- You're working on multiple features simultaneously
- You want to organize your work better
- You need to collaborate with others

## 📋 Prerequisites
- You have a Git repository set up
- You're in your project directory

---

## 🌳 Understanding Branches

**What is a branch?**
- A separate line of development
- Like a copy of your code where you can experiment
- Changes don't affect the main branch until you merge

**Default branch:** `main` (or `master` in older repos)

---

## 🔧 Basic Branch Commands

### View Branches
```bash
# List local branches
git branch

# List all branches (including remote)
git branch -a

# See current branch
git branch --show-current
```

**Example output:**
```
* main
  feature-reward-system
  feature-leaderboard
```

The `*` shows your current branch.

---

### Create a New Branch
```bash
# Create new branch
git branch feature-reward-system

# Create and switch to new branch
git checkout -b feature-reward-system

# Or using newer syntax
git switch -c feature-reward-system
```

---

### Switch Between Branches
```bash
# Switch to existing branch
git checkout main
git checkout feature-reward-system

# Or using newer syntax
git switch main
git switch feature-reward-system
```

---

### Delete a Branch
```bash
# Delete local branch (safe - prevents if not merged)
git branch -d feature-reward-system

# Force delete (even if not merged)
git branch -D feature-reward-system

# Delete remote branch
git push origin --delete feature-reward-system
```

---

## 📝 Complete Branch Workflow

### Scenario 1: Working on a New Feature

```bash
# 1. Start from main branch
git checkout main

# 2. Pull latest changes
git pull origin main

# 3. Create feature branch
git checkout -b feature/reward-system-improvements

# 4. Make your changes
# ... edit files ...

# 5. Commit changes
git add .
git commit -m "Added timestamp to reward system"

# 6. Push branch to GitHub
git push origin feature/reward-system-improvements

# 7. When done, merge to main
git checkout main
git merge feature/reward-system-improvements

# 8. Push main
git push origin main

# 9. Delete feature branch
git branch -d feature/reward-system-improvements
git push origin --delete feature/reward-system-improvements
```

---

### Scenario 2: Multiple Features Simultaneously

```bash
# Working on feature A
git checkout -b feature/leaderboard
# ... make changes ...
git add .
git commit -m "Leaderboard improvements"

# Need to work on feature B
git checkout main
git checkout -b feature/student-grouping
# ... make changes ...
git add .
git commit -m "Student grouping updates"

# Back to feature A
git checkout feature/leaderboard
# ... continue work ...

# List all your branches
git branch
```

---

### Scenario 3: Experiment Safely

```bash
# Create experimental branch
git checkout -b experiment/new-ui-design

# Try new approach
# ... make experimental changes ...
git add .
git commit -m "Trying new UI approach"

# Test it
npm run dev

# If it works, merge to main
git checkout main
git merge experiment/new-ui-design

# If it doesn't work, just delete the branch
git checkout main
git branch -D experiment/new-ui-design
```

---

## 🔄 Merging Branches

### Fast-Forward Merge (Simple)
```bash
# Switch to main
git checkout main

# Merge feature branch
git merge feature/reward-system

# Push
git push origin main
```

---

### Merge with Commit
```bash
# Merge and create merge commit
git merge --no-ff feature/reward-system

# Add message
git commit -m "Merged reward system improvements"
```

---

### Handling Merge Conflicts
```bash
# Try to merge
git merge feature/reward-system

# If conflicts occur:
# CONFLICT (content): Merge conflict in reward_sys.vue

# See conflicted files
git status

# Resolve conflicts manually or:
git checkout --ours reward_sys.vue   # Keep main version
git checkout --theirs reward_sys.vue # Keep feature version

# After resolving
git add .
git commit -m "Merged feature with conflict resolution"
```

---

## 🌐 Working with Remote Branches

### Push Branch to GitHub
```bash
# Push new branch
git push origin feature/reward-system

# Push and set upstream
git push -u origin feature/reward-system
```

---

### Pull Remote Branch
```bash
# Fetch all branches
git fetch origin

# Checkout remote branch
git checkout -b feature/reward-system origin/feature/reward-system

# Or if branch exists locally
git checkout feature/reward-system
git pull origin feature/reward-system
```

---

### List Remote Branches
```bash
# See all remote branches
git branch -r

# See all branches (local and remote)
git branch -a
```

---

## 💡 Branch Naming Conventions

### Good Branch Names
```bash
# Feature branches
git checkout -b feature/reward-system-timestamp
git checkout -b feature/student-grouping-tabs

# Bug fixes
git checkout -b fix/leaderboard-display-bug
git checkout -b fix/attendance-calculation

# Experiments
git checkout -b experiment/new-ui-design
git checkout -b experiment/performance-optimization

# Hotfixes
git checkout -b hotfix/critical-security-issue
```

### Bad Branch Names (Avoid)
```bash
git checkout -b test
git checkout -b new
git checkout -b ahmed
git checkout -b branch1
```

---

## ⚠️ Common Issues & Solutions

### Issue 1: "Cannot switch branch - uncommitted changes"

**Solution A - Commit first:**
```bash
git add .
git commit -m "WIP: Save progress"
git checkout other-branch
```

**Solution B - Stash:**
```bash
git stash
git checkout other-branch
# Later: git stash pop
```

---

### Issue 2: "Branch already exists"

**Solution:**
```bash
# Delete existing branch first
git branch -D feature/reward-system

# Create new one
git checkout -b feature/reward-system
```

---

### Issue 3: "Branch not found on remote"

**Solution:**
```bash
# Fetch latest branches
git fetch origin

# List remote branches
git branch -r

# Checkout the branch
git checkout -b feature/reward-system origin/feature/reward-system
```

---

## 🎯 Branch Strategies

### Strategy 1: Feature Branches (Recommended)
```
main
  ├── feature/reward-system
  ├── feature/leaderboard
  └── feature/student-grouping
```

**When to use:** For new features

---

### Strategy 2: Git Flow
```
main (production)
  └── develop (development)
      ├── feature/reward-system
      ├── feature/leaderboard
      └── hotfix/critical-bug
```

**When to use:** Larger projects with releases

---

### Strategy 3: Simple Main Branch
```
main (everything here)
```

**When to use:** Solo projects, simple workflows

---

## 📊 Comparing Branches

### See Differences
```bash
# Compare two branches
git diff main..feature/reward-system

# See commits in feature not in main
git log main..feature/reward-system

# See files that differ
git diff --name-only main..feature/reward-system
```

---

### See Branch History
```bash
# Graphical view
git log --oneline --graph --all

# See branch relationships
git log --graph --decorate --oneline
```

---

## 📝 Complete Example: Feature Development

```bash
# 1. Update main
git checkout main
git pull origin main

# 2. Create feature branch
git checkout -b feature/reward-system-improvements

# 3. Work on feature
# ... edit reward_sys.vue ...
git add resources/js/Pages/my_table_mnger/reward_sys/reward_sys.vue
git commit -m "Added timestamp tracking"

# ... edit StudentGrouping.vue ...
git add resources/js/Pages/my_table_mnger/reward_sys/reward_sys_comp/StudentGrouping.vue
git commit -m "Improved group display"

# 4. Push feature branch
git push -u origin feature/reward-system-improvements

# 5. Update from main (if others pushed)
git checkout main
git pull origin main
git checkout feature/reward-system-improvements
git merge main

# 6. Final testing
npm run dev
# ... test everything ...

# 7. Merge to main
git checkout main
git merge feature/reward-system-improvements

# 8. Push main
git push origin main

# 9. Clean up
git branch -d feature/reward-system-improvements
git push origin --delete feature/reward-system-improvements
```

---

## 🔗 Related Guides
- [Daily Commit & Push](./01-daily-commit-push.md) - Within branches
- [Merge Conflicts](./04-merge-conflicts.md) - When merging
- [Stash Changes](./09-stash-changes.md) - Switch branches
- [Checking Status](./03-checking-status.md) - Current branch

---

**Last Updated:** 2025-12-12
