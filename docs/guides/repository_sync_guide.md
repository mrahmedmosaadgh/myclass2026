# Guide: Managing Dual-Repository Synchronization

This project uses a dual-repository setup to manage source code and build assets separately. This is essential for production environments like Hostinger where building assets on-site is not feasible.

## 📁 Repository Structure

1.  **Main Repository** (`/`): Contains all PHP, Vue source code, and configuration.
2.  **Build Repository** (`/public/build`): Contains compiled JS, CSS, and manifest files.

---

## 🛠️ Step-by-Step Update Workflow (Local)

Follow these steps once you have completed your code changes:

### 1. Build the Assets
Ensure your local `npm run build` is successful. This updates the files in `public/build`.
```bash
npm run build
```

### 2. Push Source Code (Main Repo)
```bash
git add .
git commit -m "feat: your description | YYYY-MM-DD HH:MM | Mac"
git push origin main3-clean
```

### 3. Push Build Assets (Build Repo)
The `public/build` directory is a separate Git repository. You **must** enter it to push.
```bash
cd public/build
git add -A
git commit -m "build: update assets for [feat name] | YYYY-MM-DD HH:MM | Mac"
git push origin main
cd ../..
```

---

## 🚀 Step-by-Step Sync Workflow (Hostinger SSH)

Run these commands in your Hostinger terminal to apply the updates:

### 1. Update Source Code
```bash
git fetch origin
git reset --hard origin/main3-clean
```

### 2. Update Build Assets
```bash
cd public/build
git fetch origin
git reset --hard origin/main
cd ../..
```

### 3. Final Laravel Optimization
```bash
php artisan optimize
```

---

## ⚠️ Important Notes

*   **Hostinger Git Limits**: If `git pull` fails due to untracked files, always use `git reset --hard origin/[branch]` after fetching.
*   **Case Sensitivity**: Remember that Hostinger (Linux) is case-sensitive. Ensure your component filenames and imports match exactly.
*   **Performance**: If you notice 1800+ requests, verify that your new pages use `layout: false` if they are standalone tools.
