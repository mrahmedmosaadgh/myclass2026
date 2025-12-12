<?php
namespace App\Http\Controllers\QudratQuantitative;

use App\Http\Controllers\Controller;
use App\Models\QudratQuantitative\QdratSkill;
use App\Models\QudratQuantitative\QdratSkillLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class QdratSkillController extends Controller
{
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // إضافة العناوين
        $sheet->fromArray([
            ['Skill Name', 'Description', 'Level ID', 'Display Order'],
            ['الجمع', 'جمع عددين من رقم واحد', 1, 1],
            ['الطرح', 'طرح مباشر', 1, 2],
            ['الضرب', 'ضرب عددين ضمن جدول', 2, 3],
            ['القسمة', 'قسمة عددين صحيحين', 3, 4]
        ]);

        // تنسيق العناوين
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getStyle('A1:D1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A1:D1')->getFill()->getStartColor()->setRGB('E2E8F0');

        // ضبط عرض الأعمدة
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);

        $writer = new Xlsx($spreadsheet);
        $filename = 'qdrat_skills_template.xlsx';
        $tempPath = storage_path('app/' . $filename);
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    public function validateImport(Request $request)
    {
        $request->validate([
            'data' => 'required|array',
            'data.*' => 'required|array',
            'required_fields' => 'required|array',
            'columns' => 'required|array'
        ]);

        $data = $request->input('data');
        $requiredFields = $request->input('required_fields');
        $statuses = [];

        foreach ($data as $index => $row) {
            $status = 'new';
            $errors = [];

            // التحقق من الحقول المطلوبة
            foreach ($requiredFields as $field) {
                if (empty($row[$field])) {
                    $errors[] = "حقل {$field} مطلوب";
                }
            }

            // التحقق من وجود level_id في قاعدة البيانات
            if (!empty($row['level_id'])) {
                $levelExists = QdratSkillLevel::find($row['level_id']);
                if (!$levelExists) {
                    $errors[] = "مستوى المهارة رقم {$row['level_id']} غير موجود";
                }
            }

            // التحقق من عدم تكرار اسم المهارة
            if (!empty($row['name'])) {
                $existingSkill = QdratSkill::where('name', $row['name'])->first();
                if ($existingSkill) {
                    $status = 'update';
                }
            }

            if (!empty($errors)) {
                $status = 'error';
            }

            $statuses[] = $status;
        }

        return response()->json([
            'statuses' => $statuses
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'data' => 'required|array',
            'data.*' => 'required|array',
            'columns' => 'required|array'
        ]);

        $data = $request->input('data');
        $importBatch = Str::uuid();
        $results = ['success' => [], 'errors' => []];

        DB::beginTransaction();

        try {
            foreach ($data as $index => $row) {
                try {
                    $skillData = [
                        'name' => $row['name'],
                        'description' => $row['description'] ?? null,
                        'level_id' => !empty($row['level_id']) ? (int)$row['level_id'] : null,
                        'order' => !empty($row['order']) ? (int)$row['order'] : 0,
                        'created_by' => auth()->id(),
                        'import_batch' => $importBatch
                    ];

                    // التحقق من وجود المهارة
                    $existingSkill = QdratSkill::where('name', $row['name'])->first();
                    
                    if ($existingSkill) {
                        // تحديث المهارة الموجودة
                        $existingSkill->update($skillData);
                        $results['success'][] = "تم تحديث المهارة: {$row['name']}";
                    } else {
                        // إنشاء مهارة جديدة
                        QdratSkill::create($skillData);
                        $results['success'][] = "تم إنشاء المهارة: {$row['name']}";
                    }

                } catch (\Exception $e) {
                    $results['errors'][] = "خطأ في الصف " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'results' => $results,
                'importId' => $importBatch
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'results' => [
                    'success' => [],
                    'errors' => ['خطأ في الاستيراد: ' . $e->getMessage()]
                ]
            ], 500);
        }
    }

    public function undo($importId)
    {
        try {
            $deletedCount = QdratSkill::where('import_batch', $importId)->delete();
            
            return response()->json([
                'message' => "تم التراجع عن استيراد {$deletedCount} مهارة بنجاح"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'خطأ في التراجع عن الاستيراد: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        return QdratSkill::with(['level'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'level_id' => 'nullable|exists:qdrat_skill_levels,id',
            'order' => 'nullable|integer',
        ]);

        $data['created_by'] = auth()->id();

        return QdratSkill::create($data);
    }

    public function update(Request $request, QdratSkill $qdratSkill)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            'level_id' => 'nullable|exists:qdrat_skill_levels,id',
            'order' => 'nullable|integer',
        ]);

        $qdratSkill->update($data);
        return $qdratSkill;
    }

    public function destroy(QdratSkill $qdratSkill)
    {
        $qdratSkill->delete();
        return response()->noContent();
    }
}
