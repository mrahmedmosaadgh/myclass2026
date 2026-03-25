# Daily Commit & Push - Updating Your Repository

## ✅ When to Use This Guide
- You've finished your work for the day
- You want to save your changes to GitHub
- You want to update the repository with a descriptive comment
- Everything is working and you want to backup your code

## 📋 Prerequisites
- You have made changes to your code
- Your code is working (tested locally)
- You're in your project directory

---

## 🔧 Step-by-Step Instructions

### Step 1: Navigate to Your Project Directory
```bash
cd /Users/ahmedmosaad/Herd/myclass2026-main
```

### Step 2: Check What Has Changed
```bash
git status
```

**What you'll see:**
- Red files = Modified but not staged
- Green files = Staged and ready to commit
- Untracked files = New files not yet tracked by git

**Example output:**
```
On branch main
Changes not staged for commit:
  modified:   resources/js/Pages/my_table_mnger/reward_sys/reward_sys.vue
  modified:   resources/js/Pages/my_table_mnger/reward_sys/reward_sys_comp/StudentGrouping.vue
```

### Step 3: Add Your Changes

**Option A: Add ALL changes (most common)**
```bash
git add .
```

**Option B: Add specific files only**
```bash
git add resources/js/Pages/my_table_mnger/reward_sys/reward_sys.vue
git add resources/js/Pages/my_table_mnger/reward_sys/reward_sys_comp/StudentGrouping.vue
```

**Option C: Add all files in a directory**
```bash
git add resources/js/Pages/my_table_mnger/reward_sys/
```

### Step 4: Verify What Will Be Committed
```bash
git status
```

Now the files should appear in green, meaning they're staged.

### Step 5: Commit Your Changes with a Descriptive Message
```bash
git commit -m "Your descriptive message here"
```

**Good commit message examples:**
```bash
git commit -m "Improved reward system with timestamp tracking"
git commit -m "Fixed student grouping display issue"
git commit -m "Added leaderboard table component"
git commit -m "Refactored attendance stats with new columns"
git commit -m "Updated reward system UI and added group tabs"
```

**Bad commit message examples (avoid these):**
```bash
git commit -m "update"
git commit -m "fix"
git commit -m "changes"
```

### Step 6: Push to GitHub
```bash
git push origin main
```

**What this does:**
- `origin` = Your GitHub repository
- `main` = The main branch

**Expected output:**
```
Enumerating objects: 5, done.
Counting objects: 100% (5/5), done.
Delta compression using up to 10 threads
Compressing objects: 100% (3/3), done.
Writing objects: 100% (3/3), 1.23 KiB | 1.23 MiB/s, done.
Total 3 (delta 2), reused 0 (delta 0)
To https://github.com/mrahmedmosaadgh/myclass2026.git
   abc1234..def5678  main -> main
```

---

## ⚠️ Common Issues & Solutions

### Issue 1: "Updates were rejected because the remote contains work..."

**What happened:** Someone else (or you from another computer) pushed changes to GitHub.

**Solution:**
```bash
# Pull the latest changes first
git pull origin main

# If there are no conflicts, push again
git push origin main
```

**If you see merge conflicts, go to:** [04-merge-conflicts.md](./04-merge-conflicts.md)

---

### Issue 2: "fatal: not a git repository"

**What happened:** You're not in the right directory or git isn't initialized.

**Solution:**
```bash
# Make sure you're in the right directory
cd /Users/ahmedmosaad/Herd/myclass2026-main

# Check if .git folder exists
ls -la | grep .git

# If no .git folder, see: 02-first-time-setup.md
```

---

### Issue 3: "nothing to commit, working tree clean"

**What happened:** You haven't made any changes, or they're already committed.

**Solution:**
```bash
# Check if you need to push
git status

# If it says "Your branch is ahead of 'origin/main'", then push:
git push origin main
```

---

### Issue 4: "Please tell me who you are"

**What happened:** Git doesn't know your name/email.

**Solution:**
```bash
git config --global user.name "Ahmed Mosaad"
git config --global user.email "your-email@example.com"

# Then try committing again
git commit -m "Your message"
```

---

## 💡 Best Practices

### 1. Commit Often
- Don't wait until the end of the day
- Commit after completing each feature or fix
- Small, frequent commits are better than large ones

### 2. Write Clear Commit Messages
- Start with a verb (Added, Fixed, Updated, Removed)
- Be specific about what changed
- Keep it under 50 characters if possible

### 3. Test Before Committing
- Make sure your code works
- Check for console errors
- Test the features you changed

### 4. Review Before Pushing
```bash
# See what you're about to push
git log origin/main..HEAD

# See the actual changes
git diff origin/main
```

---

## 📝 Complete Workflow Example

Here's a complete example of a typical workflow:

```bash
# 1. Navigate to project
cd /Users/ahmedmosaad/Herd/myclass2026-main

# 2. Check status
git status

# 3. Add all changes
git add .

# 4. Verify what's staged
git status

# 5. Commit with message
git commit -m "Improved reward system with timestamp and group tabs"

# 6. Push to GitHub
git push origin main
```

**Expected time:** 1-2 minutes

---

## 🎯 Quick Commands Summary

```bash
# The essential 4 commands you'll use daily:
cd /Users/ahmedmosaad/Herd/myclass2026-main
git add .
git commit -m "Your descriptive message"
git push origin main
```

---

## 🔗 Related Guides
- [Checking Status](./03-checking-status.md) - Before committing
- [Merge Conflicts](./04-merge-conflicts.md) - If push fails
- [Undo Changes](./05-undo-changes.md) - If you made a mistake
- [Pull Latest Changes](./06-pull-latest-changes.md) - Before starting work

---

**Last Updated:** 2025-12-12
