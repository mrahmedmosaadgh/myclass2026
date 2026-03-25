# Merge Conflicts - Resolving Conflicts When Pushing

## ✅ When to Use This Guide
- You tried to push and got "Updates were rejected"
- You see "CONFLICT" messages when pulling
- Git says "Automatic merge failed; fix conflicts"
- Files have conflict markers (`<<<<<<<`, `=======`, `>>>>>>>`)

## 📋 Prerequisites
- You have uncommitted or committed changes
- You tried to push/pull and got a conflict
- You're in your project directory

---

## 🔧 Step-by-Step Instructions

### Step 1: Understand What Happened

**Scenario:** You and someone else (or you on another computer) changed the same file.

**Git's message:**
```
CONFLICT (content): Merge conflict in resources/js/Pages/reward_sys/reward_sys.vue
Automatic merge failed; fix conflicts and then commit the result.
```

---

### Step 2: Check Which Files Have Conflicts
```bash
git status
```

**You'll see:**
```
Unmerged paths:
  both modified:   resources/js/Pages/my_table_mnger/reward_sys/reward_sys.vue
  both modified:   resources/js/Pages/my_table_mnger/reward_sys/reward_sys_comp/StudentGrouping.vue
```

---

## 🎯 Resolution Strategy

You have **3 options**:

### Option 1: Keep YOUR Changes (Most Common)
Use this when your local changes are the correct ones.

```bash
# Keep your version for all conflicts
git checkout --ours .

# Add the resolved files
git add .

# Complete the merge
git commit -m "Merge remote changes, keeping local improvements"

# Push to GitHub
git push origin main
```

---

### Option 2: Keep THEIR Changes (Remote)
Use this when the GitHub version is correct.

```bash
# Keep remote version for all conflicts
git checkout --theirs .

# Add the resolved files
git add .

# Complete the merge
git commit -m "Merge remote changes, accepting remote version"

# Push to GitHub
git push origin main
```

---

### Option 3: Manually Resolve Each Conflict
Use this when you need to combine both versions.

#### Step 3a: Open the Conflicted File

Open the file in your editor. You'll see conflict markers:

```vue
<<<<<<< HEAD (Your changes)
<template>
  <div class="reward-system-new">
    <!-- Your version -->
  </div>
</template>
=======
<template>
  <div class="reward-system">
    <!-- Their version -->
  </div>
</template>
>>>>>>> origin/main (Remote changes)
```

#### Step 3b: Edit the File

**Remove the conflict markers and keep what you want:**

```vue
<template>
  <div class="reward-system-new">
    <!-- Combined or chosen version -->
  </div>
</template>
```

#### Step 3c: Mark as Resolved

```bash
# Add the resolved file
git add resources/js/Pages/my_table_mnger/reward_sys/reward_sys.vue

# Check status
git status
```

#### Step 3d: Repeat for All Conflicted Files

```bash
# See remaining conflicts
git status

# Resolve each file, then add it
git add <filename>
```

#### Step 3e: Complete the Merge

```bash
# Commit the merge
git commit -m "Resolved merge conflicts manually"

# Push to GitHub
git push origin main
```

---

## ⚠️ Common Issues & Solutions

### Issue 1: Too Many Conflicts

**Solution - Start Fresh:**
```bash
# Abort the merge
git merge --abort

# Stash your changes
git stash

# Pull fresh code
git pull origin main

# Apply your changes back
git stash pop

# Now commit and push
git add .
git commit -m "Your message"
git push origin main
```

---

### Issue 2: "error: you need to resolve your current index first"

**Solution:**
```bash
# Check what's conflicted
git status

# Either resolve conflicts or abort
git merge --abort

# Or reset to last commit
git reset --hard HEAD
```

---

### Issue 3: Accidentally Deleted Important Code

**Solution:**
```bash
# Abort the merge
git merge --abort

# Your changes are still there
git status

# Try again with a different strategy
```

---

## 💡 Best Practices

### 1. Pull Before You Push
Always pull the latest changes before starting work:
```bash
git pull origin main
```

### 2. Commit Often
Smaller, frequent commits = fewer conflicts

### 3. Communicate with Team
If working with others, coordinate who's working on what files

### 4. Use Branches
For major changes, use a separate branch:
```bash
git checkout -b feature/reward-system
# Make changes
git push origin feature/reward-system
```

---

## 📝 Complete Conflict Resolution Example

### Scenario: You tried to push and got rejected

```bash
# 1. You tried to push
git push origin main
# Error: Updates were rejected

# 2. Pull to see conflicts
git pull origin main
# CONFLICT in reward_sys.vue

# 3. Check conflicts
git status
# both modified: reward_sys.vue

# 4. Keep your version (easiest)
git checkout --ours .

# 5. Add resolved files
git add .

# 6. Complete merge
git commit -m "Merge remote changes, keeping local improvements"

# 7. Push successfully
git push origin main
```

---

## 🎯 Quick Decision Guide

**Choose your strategy:**

| Situation | Command | When to Use |
|-----------|---------|-------------|
| Your changes are correct | `git checkout --ours .` | You just finished working |
| Remote changes are correct | `git checkout --theirs .` | You haven't changed much |
| Need both versions | Manual edit | Complex changes |
| Too many conflicts | `git merge --abort` | Start over |

---

## 🔍 Understanding Conflict Markers

```
<<<<<<< HEAD
Your local changes
=======
Remote changes from GitHub
>>>>>>> origin/main
```

- `<<<<<<< HEAD` = Start of your changes
- `=======` = Separator
- `>>>>>>> origin/main` = End of remote changes

**To resolve:** Delete the markers and keep what you want.

---

## 🚨 Emergency: Abort Everything

If you're confused and want to start over:

```bash
# Abort the merge
git merge --abort

# Go back to last commit
git reset --hard HEAD

# Pull fresh
git pull origin main

# Your uncommitted changes are lost!
# (Use git stash first if you want to save them)
```

---

## 🔗 Related Guides
- [Daily Commit & Push](./01-daily-commit-push.md) - Prevent conflicts
- [Stash Changes](./09-stash-changes.md) - Save work temporarily
- [Undo Changes](./05-undo-changes.md) - If you made mistakes
- [Pull Latest Changes](./06-pull-latest-changes.md) - Before starting work

---

**Last Updated:** 2025-12-12
