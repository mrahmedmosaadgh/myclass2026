# Repository Issues - Fixing Common Git Problems

## ✅ When to Use This Guide
- Git is giving you strange errors
- Repository seems corrupted
- Can't push or pull
- Lost connection to remote
- Something just isn't working right

## 📋 Prerequisites
- You're in your project directory
- You have Git installed

---

## 🔧 Common Issues & Solutions

### Issue 1: "fatal: not a git repository"

**Symptoms:**
```bash
$ git status
fatal: not a git repository (or any of the parent directories): .git
```

**Cause:** You're not in a Git repository or `.git` folder is missing.

**Solution:**
```bash
# Check if you're in the right directory
pwd

# Navigate to project
cd /Users/ahmedmosaad/Herd/myclass2026-main

# Check if .git exists
ls -la | grep .git

# If no .git folder, initialize
git init

# Add remote
git remote add origin https://github.com/mrahmedmosaadgh/myclass2026.git

# Pull code
git pull origin main
```

---

### Issue 2: "remote origin already exists"

**Symptoms:**
```bash
$ git remote add origin https://github.com/...
fatal: remote origin already exists.
```

**Solution:**
```bash
# Check current remote
git remote -v

# Update the URL
git remote set-url origin https://github.com/mrahmedmosaadgh/myclass2026.git

# Or remove and re-add
git remote remove origin
git remote add origin https://github.com/mrahmedmosaadgh/myclass2026.git
```

---

### Issue 3: "Permission denied (publickey)"

**Symptoms:**
```bash
$ git push origin main
Permission denied (publickey).
fatal: Could not read from remote repository.
```

**Cause:** SSH authentication failed.

**Solution - Switch to HTTPS:**
```bash
# Change to HTTPS URL
git remote set-url origin https://github.com/mrahmedmosaadgh/myclass2026.git

# Try pushing again
git push origin main
```

**Solution - Fix SSH (Advanced):**
```bash
# Generate new SSH key
ssh-keygen -t ed25519 -C "your-email@example.com"

# Copy public key
cat ~/.ssh/id_ed25519.pub

# Add to GitHub:
# 1. Go to GitHub.com → Settings → SSH and GPG keys
# 2. Click "New SSH key"
# 3. Paste the key
```

---

### Issue 4: "refusing to merge unrelated histories"

**Symptoms:**
```bash
$ git pull origin main
fatal: refusing to merge unrelated histories
```

**Cause:** Local and remote have different histories.

**Solution:**
```bash
# Allow unrelated histories
git pull origin main --allow-unrelated-histories

# If conflicts, resolve them
# See: 04-merge-conflicts.md
```

---

### Issue 5: "Your branch and 'origin/main' have diverged"

**Symptoms:**
```bash
$ git status
Your branch and 'origin/main' have diverged,
and have 2 and 3 different commits each, respectively.
```

**Cause:** You and remote both have different commits.

**Solution A - Keep Your Changes:**
```bash
# Pull and merge
git pull origin main

# Resolve conflicts if any
# See: 04-merge-conflicts.md

# Push
git push origin main
```

**Solution B - Discard Your Changes:**
```bash
# WARNING: Deletes your local commits!
git reset --hard origin/main

# Verify
git status
```

---

### Issue 6: "error: failed to push some refs"

**Symptoms:**
```bash
$ git push origin main
error: failed to push some refs to 'https://github.com/...'
hint: Updates were rejected because the remote contains work...
```

**Cause:** Remote has commits you don't have.

**Solution:**
```bash
# Pull first
git pull origin main

# Then push
git push origin main
```

---

### Issue 7: "fatal: unable to access... Could not resolve host"

**Symptoms:**
```bash
$ git push origin main
fatal: unable to access 'https://github.com/...': Could not resolve host: github.com
```

**Cause:** Network connection issue.

**Solution:**
```bash
# Check internet connection
ping github.com

# If no internet, fix connection first

# Check remote URL
git remote -v

# Try again
git push origin main
```

---

### Issue 8: "detached HEAD state"

**Symptoms:**
```bash
$ git status
HEAD detached at abc1234
```

**Cause:** You checked out a specific commit instead of a branch.

**Solution:**
```bash
# Get back to main branch
git checkout main

# Or create new branch from here
git checkout -b new-branch-name
```

---

### Issue 9: ".gitignore not working"

**Symptoms:** Files that should be ignored are still tracked.

**Solution:**
```bash
# Remove files from Git tracking
git rm -r --cached .

# Re-add everything (respecting .gitignore)
git add .

# Commit
git commit -m "Fixed .gitignore"

# Push
git push origin main
```

---

### Issue 10: "Repository is too large to push"

**Symptoms:**
```bash
$ git push origin main
error: RPC failed; HTTP 413 curl 22 The requested URL returned error: 413
```

**Cause:** Large files in repository.

**Solution:**
```bash
# Find large files
find . -type f -size +10M

# Remove large files from Git history (careful!)
git filter-branch --tree-filter 'rm -f path/to/large/file' HEAD

# Or use BFG Repo-Cleaner (recommended)
# See: https://rtyley.github.io/bfg-repo-cleaner/

# Add large files to .gitignore
echo "*.mp4" >> .gitignore
echo "*.zip" >> .gitignore
echo "node_modules/" >> .gitignore
```

---

## 🚨 Nuclear Option: Complete Reset

**⚠️ WARNING: This deletes all local changes!**

Use only when everything else fails:

```bash
# 1. Backup your work first!
cp -r /Users/ahmedmosaad/Herd/myclass2026-main /Users/ahmedmosaad/Herd/myclass2026-backup

# 2. Remove .git folder
rm -rf .git

# 3. Re-initialize
git init

# 4. Add remote
git remote add origin https://github.com/mrahmedmosaadgh/myclass2026.git

# 5. Fetch
git fetch origin

# 6. Reset to remote
git reset --hard origin/main

# 7. Set tracking
git branch --set-upstream-to=origin/main main
```

---

## 🔍 Diagnostic Commands

### Check Repository Health
```bash
# Verify repository
git fsck

# Check for issues
git status

# See configuration
git config --list

# Check remote
git remote -v

# See branches
git branch -a
```

---

### Check Connection to GitHub
```bash
# Test connection
ssh -T git@github.com

# Or for HTTPS
git ls-remote origin
```

---

### See What's Different
```bash
# Compare with remote
git fetch origin
git diff origin/main

# See commit history
git log --oneline --graph --all -10
```

---

## 💡 Preventive Measures

### 1. Regular Status Checks
```bash
# Check often
git status

# Before major operations
git status
git remote -v
```

### 2. Keep .gitignore Updated
```bash
# Common entries for Laravel/Vue
node_modules/
vendor/
.env
.DS_Store
*.log
storage/
```

### 3. Regular Pulls
```bash
# Pull before starting work
git pull origin main
```

### 4. Commit Often
```bash
# Small, frequent commits
git add .
git commit -m "Small change description"
```

---

## 📝 Recovery Checklist

When something goes wrong:

- [ ] Don't panic!
- [ ] Check `git status`
- [ ] Check `git remote -v`
- [ ] Check you're in the right directory (`pwd`)
- [ ] Check internet connection
- [ ] Read the error message carefully
- [ ] Search for the specific error
- [ ] Try the solutions in this guide
- [ ] If all else fails, use nuclear option (with backup!)

---

## 🎯 Quick Fixes Summary

```bash
# Not a git repository
git init

# Wrong remote
git remote set-url origin https://github.com/mrahmedmosaadgh/myclass2026.git

# Can't push
git pull origin main
git push origin main

# Diverged branches
git pull origin main

# Detached HEAD
git checkout main

# Start fresh (with backup!)
rm -rf .git
git init
git remote add origin https://github.com/mrahmedmosaadgh/myclass2026.git
git fetch origin
git reset --hard origin/main
```

---

## 🔗 Related Guides
- [First Time Setup](./02-first-time-setup.md) - Proper initialization
- [Checking Status](./03-checking-status.md) - Diagnose issues
- [Lost Connection](./11-lost-connection.md) - Network issues
- [Corrupted Repository](./12-corrupted-repository.md) - Severe issues

---

**Last Updated:** 2025-12-12
