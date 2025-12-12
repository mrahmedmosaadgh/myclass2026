<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\School;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Stage;

trait ImportValidationTrait
{
    protected function validateImportRecord($data)
    {
        $col_db = $data['col_db'];
        $col_file = $data['col_file'];
        $row = $data['row'];
        $model = $data['model'];
        $uniqueFields = $data['uniqueFields'] ?? [];
        $requiredFields = $data['requiredFields'] ?? [];

        // Check if ID exists
        if (!empty($row[$col_file])) {
            $record = $model::where($col_db, $row[$col_file])->first();
            if ($record) {
                return 'update';
            }
        }

        // Check for duplicates based on unique fields
        if ($this->isDuplicate($row, $model, $uniqueFields)) {
            return 'duplicate';
        }

        // Check required fields
        if (!$this->hasRequiredFields($row, $requiredFields)) {
            return 'invalid';
        }

        return 'new';
    }

    protected function isDuplicate($row, $model, $uniqueFields)
    {
        if (empty($uniqueFields)) {
            return false;
        }

        $query = $model::query();
        $hasCondition = false;

        foreach ($uniqueFields as $dbField => $rowKey) {
            if (isset($row[$rowKey]) && !empty($row[$rowKey])) {
                $query->where($dbField, $row[$rowKey]);
                $hasCondition = true;
            }
        }

        return $hasCondition ? $query->exists() : false;
    }

    protected function hasRequiredFields($row, $requiredFields)
    {
        foreach ($requiredFields as $field) {
            if (empty($row[$field])) {
                return false;
            }
        }
        return true;
    }

    protected function importRecord($col_db, $col_file, $row, $model, $fieldMappings)
    {
        try {
            return DB::transaction(function () use ($col_db, $col_file, $row, $model, $fieldMappings) {
                $data = $this->mapFields($row, $fieldMappings);
                $data['user_id'] = Auth::id();

                // Look up related IDs
                $data['school_id'] = $this->getSchoolId($row['school']);
                $data['classroom_id'] = $this->getClassroomId($row['classroom']);
                $data['grade_id'] = $this->getGradeId($row['grade']);
                $data['stage_id'] = $this->getStageId($row['stage']);

                if (!empty($row[$col_file])) {
                    $record = $model::where($col_db, $row[$col_file])->first();
                    if ($record) {
                        $record->update($data);
                        return ['success', "Updated: {$row['name']}"];
                    }
                }

                $model::create($data);
                return ['success', "Created: {$row['name']}"];
            });
        } catch (\Exception $e) {
            return ['error', "Failed to import {$row['name']}: {$e->getMessage()}"];
        }
    }

    protected function mapFields($row, $fieldMappings)
    {
        $data = [];
        foreach ($fieldMappings as $excelColumn => $dbColumn) {
            if (isset($row[$excelColumn])) {
                $data[$dbColumn] = $row[$excelColumn];
            }
        }
        return $data;
    }

    // Add these helper methods to look up IDs
    private function getSchoolId($schoolName)
    {
        return School::where('name', $schoolName)->value('id');
    }

    private function getClassroomId($classroomName)
    {
        return Classroom::where('name', $classroomName)->value('id');
    }

    private function getGradeId($gradeName)
    {
        return Grade::where('name', $gradeName)->value('id');
    }

    private function getStageId($stageName)
    {
        return Stage::where('name', $stageName)->value('id');
    }
}




