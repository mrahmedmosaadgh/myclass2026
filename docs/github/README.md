# GitHub Workflow Documentation

This directory contains comprehensive guides for managing your GitHub repository in different scenarios.

## 🔄 The Daily Cycle (Start Here)
**1. [Start of Day](./00-start-of-work-day.md)** - **READ THIS FIRST**. How to update your project *before* you code.
**2. [End of Day](./01-daily-commit-push.md)** - How to save your work and update GitHub when you are done.

## 📚 Scenario Guides

### Basic Workflows
- **[Checking Status](./03-checking-status.md)** - How to check what changes you have
- **[First Time Setup](./02-first-time-setup.md)** - Setting up Git for the first time

### Common Problems
- **[Merge Conflicts](./04-merge-conflicts.md)** - What to do when you encounter merge conflicts
- **[Undo Changes](./05-undo-changes.md)** - How to undo commits or changes
- **[Pull Latest Changes](./06-pull-latest-changes.md)** - Getting the latest code (Deep Dive)
- **[Branch Management](./07-branch-management.md)** - Working with branches

### Advanced & Emergency
- **[Force Push](./08-force-push.md)** - When you need to force push (use carefully!)
- **[Stash Changes](./09-stash-changes.md)** - Temporarily save changes without committing
- **[Repository Issues](./10-repository-issues.md)** - Fixing common repository problems

## 🚀 Quick Reference

### Start of Day ☕
```bash
git pull origin main
```

### End of Day 🌙
```bash
git add .
git commit -m "Description of work"
git push origin main
```

## 📖 How to Use These Guides

1. Find the scenario that matches your situation
2. Follow the step-by-step instructions
3. Each guide includes:
   - ✅ When to use it
   - 📋 Prerequisites
   - 🔧 Step-by-step commands
   - ⚠️ Common issues and solutions
   - 💡 Tips and best practices

## 🆘 Need Help?

If you're unsure which guide to use:
1. Start with [Checking Status](./03-checking-status.md)
2. Based on what you see, choose the appropriate guide
3. If still unsure, check [Repository Issues](./10-repository-issues.md)

---
**Last Updated:** 2025-12-12
