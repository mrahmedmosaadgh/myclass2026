# 📜 Development & History Workflow

Follow these rules strictly to maintain project consistency and history.

---

## 🚀 1. Starting a New Task
Before you start coding a new feature or fixing a bug:
1.  **Sync with Main**: `git checkout main2` and `git pull`.
2.  **Creation**: Create a descriptive branch: `git checkout -b <branch_name>`.

---

## ✅ 2. Completing a Task
Once the feature or bug fix is finished and verified:

1.  **Document in History**: Create/update a history file in `docs/history/`.
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
1.  **Sync with Main**: `git checkout main2` and `git pull`.
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