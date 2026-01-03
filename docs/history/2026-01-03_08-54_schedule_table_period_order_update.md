# 2026-01-03 08:54 | Schedule Table Period Order Column Update

## Overview
Updated the original schedules table migration to include the `period_order` column directly in the table definition, along with an index for improved query performance. This resolves the "Unknown column 'period_order'" error that was occurring during schedule copy operations.

## Key Changes
- Added `period_order` column to the original `create_schedules_table` migration
- Added composite index `['copy_id', 'cst_id', 'period_order']` for improved query performance
- Removed the need for a separate migration to add this column
- Maintained consistency between the model definition and the database schema

## Technical Details
The application was attempting to query a `period_order` column that existed in the Schedule model's `$fillable` array but was missing from the actual database table. This caused SQL errors when performing schedule copy operations. The column has now been properly integrated into the original migration file to ensure consistency between the model and the database schema from the initial table creation.