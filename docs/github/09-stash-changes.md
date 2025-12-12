# Stash Changes - Temporarily Save Your Work

## ✅ When to Use This Guide
- You need to pull but have uncommitted changes
- You want to switch branches without committing
- You need to quickly save work to test something
- You want to clean your working directory temporarily
- You're not ready to commit but need a clean state

## 📋 Prerequisites
- You have uncommitted changes
- You're in your project directory

---

## 🔧 Basic Stash Commands

### Save Your Changes
```bash
# Stash all changes with a message
git stash save "Working on reward system feature"

# Or simply
git stash

# Verify working directory is clean
git status
```

**What this does:**
- Saves your uncommitted changes
- Reverts files to last commit state
- Keeps changes in a "stash" for later

---

### Restore Your Changes
```bash
# Restore most recent stash
git stash pop

# Or apply without removing from stash
git stash apply
```

**Difference:**
- `pop` = Apply and remove from stash list
- `apply` = Apply but keep in stash list

---

## 📝 Complete Stash Workflow

### Scenario 1: Need to Pull with Uncommitted Changes

```bash
# 1. You have changes
git status
# Shows: modified files

# 2. Stash them
git stash save "Work in progress on reward system"

# 3. Pull latest code
git pull origin main

# 4. Restore your changes
git stash pop

# 5. Continue working
```

---

### Scenario 2: Quick Test Without Committing

```bash
# 1. Save current work
git stash save "Before testing alternative approach"

# 2. Make experimental changes
# ... edit files ...

# 3. Test the changes
# ... run tests ...

# 4. If experiment failed, restore original
git reset --hard HEAD
git stash pop

# 5. If experiment succeeded, commit
git add .
git commit -m "Implemented new approach"
# Discard stash
git stash drop
```

---

### Scenario 3: Switch Context Quickly

```bash
# Working on feature A
# Suddenly need to fix urgent bug

# 1. Stash feature A work
git stash save "Feature A - half done"

# 2. Fix the urgent bug
git add .
git commit -m "Fixed urgent bug"
git push origin main

# 3. Resume feature A
git stash pop
```

---

## 🔍 Managing Multiple Stashes

### List All Stashes
```bash
git stash list
```

**Example output:**
```
stash@{0}: On main: Working on reward system feature
stash@{1}: On main: Experimental UI changes
stash@{2}: On main: WIP - student grouping
```

---

### Apply Specific Stash
```bash
# Apply stash by index
git stash apply stash@{1}

# Pop specific stash
git stash pop stash@{1}
```

---

### View Stash Contents
```bash
# See what's in most recent stash
git stash show

# See detailed changes
git stash show -p

# See specific stash
git stash show -p stash@{1}
```

---

### Delete Stashes
```bash
# Delete most recent stash
git stash drop

# Delete specific stash
git stash drop stash@{1}

# Delete ALL stashes (careful!)
git stash clear
```

---

## ⚠️ Common Issues & Solutions

### Issue 1: Stash Pop Causes Conflicts

**What happened:** Your stashed changes conflict with current code.

**You'll see:**
```
CONFLICT (content): Merge conflict in reward_sys.vue
The stash entry is kept in case you need it again.
```

**Solution:**
```bash
# 1. Resolve conflicts in files (remove conflict markers)
# Edit the files manually

# 2. Add resolved files
git add .

# 3. Drop the stash (it's already applied)
git stash drop

# Or keep the stash
# (it stays in the list)
```

---

### Issue 2: "No local changes to save"

**What happened:** You don't have any uncommitted changes.

**Solution:**
```bash
# Check status
git status

# If clean, nothing to stash
```

---

### Issue 3: Lost Track of Stashes

**Solution:**
```bash
# List all stashes with details
git stash list

# See what each contains
git stash show -p stash@{0}
git stash show -p stash@{1}
```

---

## 💡 Advanced Stash Techniques

### Stash Only Staged Changes
```bash
# Stash only what's in staging area
git stash push --staged
```

---

### Stash Specific Files
```bash
# Stash only certain files
git stash push -m "Stashing reward_sys.vue" resources/js/Pages/my_table_mnger/reward_sys/reward_sys.vue
```

---

### Stash Including Untracked Files
```bash
# Stash everything including new files
git stash save --include-untracked "All changes including new files"

# Or short form
git stash -u
```

---

### Create Branch from Stash
```bash
# Create new branch with stashed changes
git stash branch feature-reward-system stash@{0}
```

**What this does:**
- Creates new branch
- Applies stash to it
- Drops the stash

---

## 📊 Stash vs Commit

| Feature | Stash | Commit |
|---------|-------|--------|
| Saves changes | ✅ | ✅ |
| In history | ❌ | ✅ |
| Can be shared | ❌ | ✅ |
| Temporary | ✅ | ❌ |
| Quick to use | ✅ | ❌ |

**Use stash when:**
- Changes are incomplete
- Need quick context switch
- Not ready to commit

**Use commit when:**
- Changes are complete
- Want to save in history
- Want to share with others

---

## 📝 Complete Examples

### Example 1: Pull with Uncommitted Changes
```bash
# Current state: modified files
git status

# Save changes
git stash save "WIP: Reward system improvements"

# Pull latest
git pull origin main

# Restore changes
git stash pop

# Continue working
```

---

### Example 2: Multiple Stashes
```bash
# Working on feature A
git stash save "Feature A: Reward system"

# Start feature B
# ... make changes ...
git stash save "Feature B: Student grouping"

# Start feature C
# ... make changes ...
git stash save "Feature C: Leaderboard"

# List all
git stash list

# Work on feature B
git stash apply stash@{1}
# ... finish feature B ...
git add .
git commit -m "Completed feature B"
git stash drop stash@{1}

# Work on feature A
git stash pop stash@{0}
```

---

### Example 3: Safe Experimentation
```bash
# Save current work
git stash save "Before refactoring"

# Try new approach
# ... make experimental changes ...

# Test it
npm run dev

# If it works
git add .
git commit -m "Successful refactoring"
git stash drop

# If it doesn't work
git reset --hard HEAD
git stash pop
```

---

## 🎯 Quick Commands Summary

```bash
# Save changes
git stash
git stash save "Message"

# Restore changes
git stash pop          # Apply and remove
git stash apply        # Apply and keep

# View stashes
git stash list         # List all
git stash show         # Show latest
git stash show -p      # Show with diff

# Manage stashes
git stash drop         # Delete latest
git stash clear        # Delete all
```

---

## 💡 Best Practices

### 1. Use Descriptive Messages
```bash
# Good
git stash save "Reward system: Added timestamp tracking"

# Bad
git stash save "WIP"
git stash
```

### 2. Don't Keep Too Many Stashes
```bash
# Regularly clean up
git stash list

# Drop old stashes
git stash drop stash@{5}
```

### 3. Pop vs Apply
```bash
# Use pop if you're done with the stash
git stash pop

# Use apply if you want to keep it
git stash apply
```

### 4. Check Before Stashing
```bash
# See what will be stashed
git status
git diff
```

---

## 🔗 Related Guides
- [Pull Latest Changes](./06-pull-latest-changes.md) - Common use case
- [Undo Changes](./05-undo-changes.md) - Alternative to stash
- [Branch Management](./07-branch-management.md) - Another way to save work
- [Daily Commit & Push](./01-daily-commit-push.md) - Permanent save

---

**Last Updated:** 2025-12-12
