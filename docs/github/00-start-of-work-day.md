# Start of Work Day - Getting Latest Changes

## ✅ When to Use This Guide
- **IMMEDIATELY** when you sit down to work
- **BEFORE** you write any code
- **BEFORE** you open detailed files
- If you're switching computers
- If your teammate just told you "I pushed my changes"

## 🎯 The Golden Rule
**"Always Pull Before You Start."** 
This prevents 90% of merge conflicts and headaches later.

---

## 🔧 Step-by-Step Instructions

### Step 1: Navigate to Project
```bash
cd /Users/ahmedmosaad/Herd/myclass2026-main
```

### Step 2: Ensure You Have a Clean Slate
Before pulling, make sure you don't have uncommitted changes from yesterday (if you forgot to push).

```bash
git status
```

**Scenario A: "working tree clean" (Perfect)**
```
On branch main
Your branch is up to date with 'origin/main'.
nothing to commit, working tree clean
```
👉 **Go to Step 3.**

**Scenario B: You have unstaged changes**
```
Changes not staged for commit:
modified: reward_sys.vue
```
👉 **Decision:**
- If you want to keep them: `git stash` (Saves them temporarily)
- If they are trash: `git checkout .` (Deletes them)
- Then **Go to Step 3.**

### Step 3: Pull the Latest Changes
This downloads changes from GitHub and merges them into your computer.

```bash
git pull origin main
```

### Step 4: Verify the Update

**Result A: Already up to date**
```
Already up to date.
```
✅ **You are ready to code!** No changes happened while you were away.

**Result B: Updates downloaded**
```
Updating a1b2c3d..e5f6g7h
Fast-forward
 .../StudentGrouping.vue | 45 +++++++++++++++++
 1 file changed, 45 insertions(+)
```
✅ **Success!** You now have the latest team code. **You are ready to code!**

**Result C: Conflict**
```
CONFLICT (content): Merge conflict in ...
```
⚠️ **Stop.** You have conflicts. See [Merge Conflicts Guide](./04-merge-conflicts.md).

---

## 🔄 The Complete Daily Cycle

1.  **☕ Start of Day:**
    - `git status`
    - `git pull origin main`
    - *Start Coding...*

2.  **💻 During Day (Optional - if others are pushing):**
    - `git stash` (save your WIP)
    - `git pull origin main`
    - `git stash pop` (restore your WIP)

3.  **🌙 End of Day:**
    - `git add .`
    - `git commit -m "Your work description"`
    - `git push origin main`

---

## 🔗 Next Steps
- Now that you are synced, start your work!
- When you are finished, go to [Daily Commit & Push](./01-daily-commit-push.md)

**Last Updated:** 2025-12-12
