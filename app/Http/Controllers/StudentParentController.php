<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\StudentParent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;

class StudentParentController extends Controller
{
    public function index()
    {
        $parents = StudentParent::with('school')
            ->paginate(10);

        return Inertia::render('my_class/admin/StudentParents/Index', [
            'records' => $parents,
            'schools' => \App\Models\School::select('id', 'name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'data' => 'nullable|json',
            'report' => 'required|boolean'  // Make sure report is required and boolean
        ]);

        // Ensure report is properly cast to boolean
        $validated['report'] = (bool) $validated['report'];

        StudentParent::create($validated);

        return redirect()->back()->with('success', 'Parent created successfully');
    }

    public function update(Request $request, StudentParent $studentParent)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'data' => 'nullable|json',
            'report' => 'required|boolean'  // Make sure report is required and boolean
        ]);

        // Ensure report is properly cast to boolean
        $validated['report'] = (bool) $validated['report'];

        $studentParent->update($validated);

        return redirect()->back()->with('success', 'Parent updated successfully');
    }

    public function destroy(StudentParent $studentParent)
    {
        $studentParent->delete();
        return redirect()->back()->with('success', 'Parent deleted successfully');
    }

    public function export()
    {
        try {
            $parents = StudentParent::with('school')->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $sheet->setCellValue('A1', 'Name');
            $sheet->setCellValue('B1', 'Arabic Name');
            $sheet->setCellValue('C1', 'School');
            $sheet->setCellValue('D1', 'Enable Reports');

            // Add data
            $row = 2;
            foreach ($parents as $parent) {
                $sheet->setCellValue('A' . $row, $parent->name);
                $sheet->setCellValue('B' . $row, $parent->name_ar);
                $sheet->setCellValue('C' . $row, $parent->school->name);
                $sheet->setCellValue('D' . $row, $parent->report ? 'Enabled' : 'Disabled');
                $row++;
            }

            $writer = new Xlsx($spreadsheet);

            $filename = 'parents_' . date('Y-m-d_His') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function validateImport(Request $request)
    {
         $data = $request->input('data', []);
        $statuses = [];

        foreach ($data as $row) {
            $status = 'new';

            // Check if parent exists
             $existingSchool = School::where('name', $row['School'])->first();
            if(!$existingSchool){
                return 'no school foun for parent:'.$row['Name'];
            }
            $existingParent = StudentParent::where('name', $row['Name'])
            ->where('school_id', $existingSchool['id'])

                ->first();

            if ($existingParent) {
                $status = 'update';
            }

            // Add validation status
            $statuses[] = $status;
        }

        return response()->json(['statuses' => $statuses]);
    }

    public function import(Request $request)
    {
        $data = $request->input('data', []);
        $results = ['success' => [], 'errors' => []];
        $importId = uniqid();

        DB::beginTransaction();
        try {
            foreach ($data as $row) {
                $school = School::where('name', $row['School'])->first();

                if (!$school) {
                    throw new \Exception("School not found: {$row['School']}");
                }

                $parentData = [
                    'name' => $row['Name'],
                    'name_ar' => $row['Arabic Name'] ?? null,
                    'school_id' => $school->id,
                    'report' => $row['Enable Reports'] === 'Enabled' ? true : false,
                    'import_id' => $importId
                ];

                $existingParent = StudentParent::where('name', $row['Name'])
                    ->where('school_id', $school->id)
                    ->first();

                if ($existingParent) {
                    $existingParent->update($parentData);
                    $results['success'][] = "Updated: {$row['Name']}";
                } else {
                    StudentParent::create($parentData);
                    $results['success'][] = "Created: {$row['Name']}";
                }
            }

            DB::commit();
            return response()->json([
                'results' => $results,
                'importId' => $importId
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $results['errors'][] = $e->getMessage();
            return response()->json(['results' => $results], 422);
        }
    }

    public function undoImport($importId)
    {
        try {
            StudentParent::where('import_id', $importId)->delete();
            return response()->json(['message' => 'Import undone successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to undo import'], 500);
        }
    }
}




