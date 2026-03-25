# First Time Setup - Initializing Git & GitHub

## ✅ When to Use This Guide
- You just cloned the repository for the first time
- You're setting up Git on a new computer
- You need to configure Git with your information
- The repository lost its `.git` folder

## 📋 Prerequisites
- You have Git installed on your Mac
- You have a GitHub account
- You know your repository URL

---

## 🔧 Step-by-Step Instructions

### Step 1: Check if Git is Installed
```bash
git --version
```

**Expected output:**
```
git version 2.39.0 (or similar)
```

**If not installed:**
```bash
# Install Git using Homebrew
brew install git
```

---

### Step 2: Configure Your Git Identity
This tells Git who you are for all commits.

```bash
# Set your name
git config --global user.name "Ahmed Mosaad"

# Set your email (use your GitHub email)
git config --global user.email "your-email@example.com"

# Verify the configuration
git config --global --list
```

**Expected output:**
```
user.name=Ahmed Mosaad
user.email=your-email@example.com
```

---

### Step 3: Configure Git Settings
```bash
# Set default branch name to 'main'
git config --global init.defaultBranch main

# Set merge strategy (prevents errors when pulling)
git config --global pull.rebase false

# Enable colored output
git config --global color.ui auto

# Set default editor (optional)
git config --global core.editor "nano"
```

---

### Step 4: Navigate to Your Project Directory
```bash
cd /Users/ahmedmosaad/Herd/myclass2026-main
```

---

### Step 5: Check if Git is Already Initialized
```bash
ls -la | grep .git
```

**If you see `.git`:** Git is already initialized, skip to Step 7.

**If you don't see `.git`:** Continue to Step 6.

---

### Step 6: Initialize Git Repository
```bash
git init
```

**Expected output:**
```
Initialized empty Git repository in /Users/ahmedmosaad/Herd/myclass2026-main/.git/
```

---

### Step 7: Add Remote Repository
```bash
# Add your GitHub repository as 'origin'
git remote add origin https://github.com/mrahmedmosaadgh/myclass2026.git

# Verify the remote was added
git remote -v
```

**Expected output:**
```
origin  https://github.com/mrahmedmosaadgh/myclass2026.git (fetch)
origin  https://github.com/mrahmedmosaadgh/myclass2026.git (push)
```

**If you get an error "remote origin already exists":**
```bash
# Remove the existing remote
git remote remove origin

# Add it again
git remote add origin https://github.com/mrahmedmosaadgh/myclass2026.git
```

---

### Step 8: Fetch Latest Changes from GitHub
```bash
git fetch origin
```

**Expected output:**
```
remote: Enumerating objects: 3203, done.
remote: Counting objects: 100% (3203/3203), done.
...
From https://github.com/mrahmedmosaadgh/myclass2026
 * [new branch]      main       -> origin/main
```

---

### Step 9: Set Up Tracking Branch
```bash
# If you have no local commits yet:
git branch -M main
git branch --set-upstream-to=origin/main main

# Pull the latest code
git pull origin main --allow-unrelated-histories
```

---

## ⚠️ Common Issues & Solutions

### Issue 1: "fatal: refusing to merge unrelated histories"

**Solution:**
```bash
git pull origin main --allow-unrelated-histories
```

---

### Issue 2: "Permission denied (publickey)"

**What happened:** You need to set up SSH keys or use HTTPS with credentials.

**Solution - Use HTTPS (Easier):**
```bash
# Make sure you're using HTTPS URL
git remote set-url origin https://github.com/mrahmedmosaadgh/myclass2026.git
```

**Solution - Set up SSH (More Secure):**
```bash
# Generate SSH key
ssh-keygen -t ed25519 -C "your-email@example.com"

# Copy the public key
cat ~/.ssh/id_ed25519.pub

# Add this key to GitHub:
# 1. Go to GitHub.com
# 2. Settings → SSH and GPG keys
# 3. New SSH key
# 4. Paste the key
```

---

### Issue 3: "remote origin already exists"

**Solution:**
```bash
# Update the existing remote URL
git remote set-url origin https://github.com/mrahmedmosaadgh/myclass2026.git

# Or remove and re-add
git remote remove origin
git remote add origin https://github.com/mrahmedmosaadgh/myclass2026.git
```

---

## 💡 Best Practices

### 1. Use Global Configuration
Set up your Git config globally so you don't have to do it for each project:
```bash
git config --global user.name "Ahmed Mosaad"
git config --global user.email "your-email@example.com"
```

### 2. Create a .gitignore File
Make sure you have a `.gitignore` to exclude sensitive files:
```bash
# Check if .gitignore exists
cat .gitignore

# Common entries for Laravel/Vue projects:
# node_modules/
# .env
# vendor/
# storage/
```

### 3. Verify Your Setup
```bash
# Check Git version
git --version

# Check your configuration
git config --list

# Check remote repository
git remote -v

# Check current branch
git branch
```

---

## 📝 Complete Setup Example

Here's the complete setup from scratch:

```bash
# 1. Configure Git globally
git config --global user.name "Ahmed Mosaad"
git config --global user.email "your-email@example.com"
git config --global init.defaultBranch main
git config --global pull.rebase false

# 2. Navigate to project
cd /Users/ahmedmosaad/Herd/myclass2026-main

# 3. Initialize Git (if needed)
git init

# 4. Add remote repository
git remote add origin https://github.com/mrahmedmosaadgh/myclass2026.git

# 5. Fetch and pull
git fetch origin
git branch -M main
git pull origin main --allow-unrelated-histories

# 6. Verify setup
git status
```

---

## 🎯 Verification Checklist

After setup, verify everything works:

- [ ] `git --version` shows Git is installed
- [ ] `git config --global --list` shows your name and email
- [ ] `git remote -v` shows your GitHub repository
- [ ] `git status` works without errors
- [ ] `git branch` shows you're on `main` branch

---

## 🔗 Related Guides
- [Daily Commit & Push](./01-daily-commit-push.md) - After setup
- [Checking Status](./03-checking-status.md) - Verify everything works
- [Repository Issues](./10-repository-issues.md) - If something goes wrong

---

**Last Updated:** 2025-12-12
