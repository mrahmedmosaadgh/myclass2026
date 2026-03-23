# 📜 Development & History Workflow

Follow these rules strictly to maintain project consistency and history.

---

## 🚀 1. Starting a New Task
Before you start coding a new feature or fixing a bug:
1.  **Sync with Main**: `git checkout main3` and `git pull`.
2.  **Creation**: Create a descriptive branch: `git checkout -b <branch_name>`.

---

## ✅ 2. Completing a Task
Once the feature or bug fix is finished and verified:

1.  **Document in History**: Create  a history file in `docs/history/`.
    - **Format**: `<TIMESTAMP>_history_feature_name.md`
    - **Title**: Start the file content with `# <TIMESTAMP> | Title`.
2.  **Stage Changes**: `git add .`
3.  **Commit**: Use the mandatory detailed message format:
    ```bash
    git commit -m "Brief Subject | <TIMESTAMP> | <DEVICE_INFO> | Detailed description of changes"
    ```
    > [!IMPORTANT]
    > Ensure the comment reflects the *why* and *what* of the changes clearly.

4.  **Push**: `git push origin <branch_name>`.

---



## 📝 3. History File Guidelines
- Always place files in `docs/history/`.
- Use a new file for major features, or append to the daily file for smaller fixes.
- **Title Prefix**: Every history file entry must start with a timestamped title for easy tracking.

---

## 💾 4. Database Safety
If any changes involve database columns or tables:
1.  **Backup Database**: ask me to manual database bacup.
 
3.  **Migration Backup**: Backup the migration files specifically if requested.
# 📜 Development & History Workflow

Follow these rules strictly to maintain project consistency and history.

---

## 🚀 1. Starting a New Task
Before you start coding a new feature or fixing a bug:
1.  **Sync with Main**: `git checkout <branch_name>` and `git pull`.
2.  **Creation**: Create a descriptive branch: `git checkout -b <branch_name>`.

---

## ✅ 2. Completing a Task
Once the feature or bug fix is finished and verified:

1.  **Document in History**: Create/update a history file in `docs/history/`.
    - **Format**: `YYYY-MM-DD_HH-MM_history_feature_name.md`
    - **Title**: Start the file content with `# YYYY-MM-DD HH:MM | Title`.
2.  **Stage Changes**: `git add .`
3.  **Commit**: Use the mandatory detailed message format:
    ```bash
    git commit -m "Brief Subject | <TIMESTAMP> | <DEVICE_INFO> | Detailed description of changes"
    ```
    > [!IMPORTANT]
    > Ensure the comment reflects the *why* and *what* of the changes clearly.

4.  **Push**: `git push origin <branch_name>`.

---

## 📝 3. History File Guidelines
- Always place files in `docs/history/`.
- Use a new file for major features, or append to the daily file for smaller fixes.
- **Title Prefix**: Every history file entry must start with a timestamped title for easy tracking.

---

## 💾 4. Database Safety
If any changes involve database columns or tables:
1.  **Backup Database**: ask me to manual database bacup.
 
3.  **Migration Backup**: Backup the migration files specifically if requested.



php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

php artisan serve
npm run dev
npm run build
php artisan migrate
php artisan db:seed

php artisan menu:sync

php artisan serve
npm run dev


test card instead:

Card Number: 4111 1111 1111 1111
Expiry: 12 / 26
CVC: 123

**Git Sync Safety Check:**
⚠️ *Wait! Before committing, did you pull recent changes from other PCs?*
If you forgot to pull before starting work, don't just `git pull` as it might create conflicts or overwrite your work. Instead, use:
`git pull --rebase --autostash` (This automatically saves your local uncommitted work, fetches the remote updates, applies them, and then re-applies your work on top!).

Then, create the required history file in docs/history (with what you did and what still need to be done if you have) with the correct timestamped name and title `YYYY-MM-DD_HH-MM_history_feature_name.md`, stage the files, commit with the specified message format, and push to your current branch.

npm run build
public/build is a separate git repository.

Repository Info:

Remote: https://github.com/mrahmedmosaadgh/myclass2026_build.git
Status: It has many untracked files (the new build assets).

add all files, commit them, and push to this myclass2026_build repository 




  try to remember this while creating any page the title of the page very easy   <Head title="My Page" /> and no need to imprt it its is imported in app.js


---------------------------
 git add docs/history/2026-02-08_05-40_exam_system_fixes_and_menu_sync.md && git commit -m "docs: add history file for exam system fixes and menu sync" && git push

 cd public/build && git add . && git commit -m "build: update assets for exam fixes and menu sync" && git push origin main