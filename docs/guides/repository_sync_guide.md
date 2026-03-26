# Guide: Managing Production Deployment with Submodules

This project uses a dual-repository setup to manage source code and build assets. We use **Git Submodules** to link the source code repo to the build repo.

## 📁 Repository Structure

1.  **Main Repository** (`/`): Contains all PHP, Vue source code, and configuration. (Branch: `production`)
2.  **Build Repository** (`/public/build`): Contains compiled assets. (Linked as a **Git Submodule**)

---

## 🛠️ Step-by-Step Update Workflow (Local)

Once you've made code changes:

### 1. Build and Push Assets (Build Repo)
```bash
npm run build
cd public/build
git add -A
git commit -m "build: update for [feature] | 2026-03-26 | Mac"
git push origin main
cd ../..
```

### 2. Update and Push Source Code (Main Repo)
When you update `public/build`, the main repo sees a "change" in the submodule pointer. You **must** commit this.
```bash
git add .
git commit -m "feat: your description | 2026-03-26 | Mac"
git push origin production
```

---

## 🚀 Step-by-Step Sync Workflow (Hostinger SSH)

Run these **one-time** setup commands first (to clean old files):
```bash
# ONE TIME ONLY: Remove old build dir to allow submodule to take over
rm -rf public/build
```

Then run these to sync updates:
```bash
# 1. Update Source Code
git fetch origin
git reset --hard origin/production

# 2. Update Build Assets (Submodule)
# This one command replaces the old "cd public/build && git pull" step
git submodule update --init --remote

# 3. Final Laravel Optimization
php artisan optimize
```

---

## ⚠️ Important Notes

*   **Default Branch**: We now use `production` as the master branch for deployments.
*   **Submodule Status**: If `git status` shows "modified: public/build (new commits)", it means your build repo is ahead of your current main repo pointer. Always commit the build folder in the main repo after pushing to the build repo.
*   **Hostinger Reset**: If you get errors, always use `git reset --hard origin/production` first to ensure a clean slate.
