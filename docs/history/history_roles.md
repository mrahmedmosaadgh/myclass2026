1- start coding by these steps:
    - git checkout main2
    - git pull
    - git checkout -b <branch_name>
    
    2- every time you finish a feature or bug fix, do these steps:
    -add it to history
    - git add .
    - git commit -m "<commit_message>" with timestamp and device info: <timestamp> | <device_info>
    - git push origin <branch_name>
    3- if eny database change col or table, do these steps:
    - packup database.
    mysqldump -u root myclass2026 > backup_2025-12-27_144216.sql && ls -lh backup_2025-12-27_144216.sql

    php artisan schema:dump --path=database/schema/mysql-schema.sql && cp database/schema/mysql-schema.sql backup_2025-12-27_144216.sql && ls -lh backup_2025-12-27_144216.sql

    cp -r database/migrations/ migrations_backup_2025-12-27_144216/ && zip -r database_schema_backup_2025-12-27.zip migrations_backup_2025-12-27_144216/ && rm -rf migrations_backup_2025-12-27_144216/

git add . && git commit -m "Improving academic calendar with Quasar | 2025-12-27T14:42:16+03:00 | Ahmed’s Mac mini" && git push origin main2

4- history file should start with current "date_time_title".