<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\QuestionType;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuestionImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user with teacher role
        $this->user = User::factory()->create();
        
        // Seed question types
        $this->seedQuestionTypes();
        
        // Create test grade and subject
        Grade::create(['name' => 'Grade 10']);
        Subject::create(['name' => 'Mathematics']);
    }

    protected function seedQuestionTypes(): void
    {
        $questionTypes = [
            ['slug' => 'multiple_choice', 'name' => 'Multiple Choice', 'has_options' => true, 'supports_hints' => true, 'supports_explanation' => true],
            ['slug' => 'multi_select', 'name' => 'Multi Select', 'has_options' => true, 'supports_hints' => true, 'supports_explanation' => true],
            ['slug' => 'true_false', 'name' => 'True/False', 'has_options' => true, 'supports_hints' => true, 'supports_explanation' => true],
            ['slug' => 'fill_blank', 'name' => 'Fill in the Blank', 'has_options' => false, 'supports_hints' => true, 'supports_explanation' => true],
            ['slug' => 'short_answer', 'name' => 'Short Answer', 'has_options' => false, 'supports_hints' => true, 'supports_explanation' => true],
            ['slug' => 'essay', 'name' => 'Essay', 'has_options' => false, 'supports_hints' => true, 'supports_explanation' => true],
        ];

        foreach ($questionTypes as $type) {
            QuestionType::create($type);
        }
    }

    /** @test */
    public function it_can_import_questions_from_csv()
    {
        Storage::fake('local');

        // Create a CSV file with test data
        $csvContent = "question_type,grade_level,subject,topic,bloom_level,difficulty_level,estimated_time_sec,question_text,option_a,option_b,option_c,option_d,correct_answer,hints,explanation,status\n";
        $csvContent .= "multiple_choice,Grade 10,Mathematics,Algebra,3,2,120,\"What is 2+2?\",3,4,5,6,B,\"Think about basic addition\",\"2+2 equals 4\",active\n";
        $csvContent .= "true_false,Grade 10,Mathematics,Geometry,2,1,60,\"A triangle has 3 sides.\",True,False,,,A,,\"This is a basic property of triangles.\",active\n";

        $file = UploadedFile::fake()->createWithContent('questions.csv', $csvContent);

        // Make the import request
        $response = $this->actingAs($this->user)
            ->postJson('/api/questions/import', [
                'file' => $file,
            ]);

        // Assert the response
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'total_rows' => 2,
                'successful' => 2,
                'failed' => 0,
            ],
        ]);

        // Assert questions were created in database
        $this->assertDatabaseCount('questions', 2);
        $this->assertDatabaseHas('questions', [
            'question_text' => 'What is 2+2?',
        ]);
        $this->assertDatabaseHas('questions', [
            'question_text' => 'A triangle has 3 sides.',
        ]);

        // Assert options were created
        $this->assertDatabaseCount('question_options', 6); // 4 options for first question, 2 for second
    }

    /** @test */
    public function it_validates_required_file()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/questions/import', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function it_validates_file_type()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('questions.pdf', 100);

        $response = $this->actingAs($this->user)
            ->postJson('/api/questions/import', [
                'file' => $file,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function it_handles_invalid_question_type()
    {
        Storage::fake('local');

        $csvContent = "question_type,grade_level,subject,question_text\n";
        $csvContent .= "invalid_type,Grade 10,Mathematics,\"What is 2+2?\"\n";

        $file = UploadedFile::fake()->createWithContent('questions.csv', $csvContent);

        $response = $this->actingAs($this->user)
            ->postJson('/api/questions/import', [
                'file' => $file,
            ]);

        // Should return partial success with errors
        $response->assertStatus(422); // All failed
        $response->assertJson([
            'success' => false,
            'data' => [
                'total_rows' => 1,
                'successful' => 0,
                'failed' => 1,
            ],
        ]);

        $this->assertDatabaseCount('questions', 0);
    }

    /** @test */
    public function it_handles_missing_required_columns()
    {
        Storage::fake('local');

        // Missing question_text column
        $csvContent = "question_type,grade_level,subject\n";
        $csvContent .= "multiple_choice,Grade 10,Mathematics\n";

        $file = UploadedFile::fake()->createWithContent('questions.csv', $csvContent);

        $response = $this->actingAs($this->user)
            ->postJson('/api/questions/import', [
                'file' => $file,
            ]);

        $response->assertStatus(500); // Server error due to missing column
    }

    /** @test */
    public function it_creates_grade_levels_and_subjects_if_not_exist()
    {
        Storage::fake('local');

        $csvContent = "question_type,grade_level,subject,question_text,option_a,option_b,correct_answer,status\n";
        $csvContent .= "multiple_choice,Grade 11,Physics,\"What is gravity?\",Force,Energy,A,active\n";

        $file = UploadedFile::fake()->createWithContent('questions.csv', $csvContent);

        $response = $this->actingAs($this->user)
            ->postJson('/api/questions/import', [
                'file' => $file,
            ]);

        $response->assertStatus(200);

        // Assert new grade level and subject were created
        $this->assertDatabaseHas('grades', ['name' => 'Grade 11']);
        $this->assertDatabaseHas('subjects', ['name' => 'Physics']);
    }

    /** @test */
    public function it_imports_questions_with_hints_and_explanations()
    {
        Storage::fake('local');

        $csvContent = "question_type,grade_level,subject,question_text,option_a,option_b,correct_answer,hints,explanation,status\n";
        $csvContent .= "multiple_choice,Grade 10,Mathematics,\"What is 2+2?\",3,4,B,\"Hint 1;Hint 2\",\"Explanation text\",active\n";

        $file = UploadedFile::fake()->createWithContent('questions.csv', $csvContent);

        $response = $this->actingAs($this->user)
            ->postJson('/api/questions/import', [
                'file' => $file,
            ]);

        $response->assertStatus(200);

        // Assert question has hints and explanation
        $question = \App\Models\Question::first();
        $this->assertNotNull($question->hints);
        $this->assertIsArray($question->hints);
        $this->assertCount(2, $question->hints);
        $this->assertEquals('Hint 1', $question->hints[0]);
        $this->assertEquals('Hint 2', $question->hints[1]);

        $this->assertNotNull($question->explanation);
        $this->assertIsArray($question->explanation);
        $this->assertEquals('Explanation text', $question->explanation['text']);
    }

    /** @test */
    public function it_imports_multi_select_questions_with_multiple_correct_answers()
    {
        Storage::fake('local');

        $csvContent = "question_type,grade_level,subject,question_text,option_a,option_b,option_c,option_d,correct_answer,status\n";
        $csvContent .= "multi_select,Grade 10,Mathematics,\"Select all even numbers\",2,3,4,5,\"A,C\",active\n";

        $file = UploadedFile::fake()->createWithContent('questions.csv', $csvContent);

        $response = $this->actingAs($this->user)
            ->postJson('/api/questions/import', [
                'file' => $file,
            ]);

        $response->assertStatus(200);

        // Assert correct options are marked as correct
        $question = \App\Models\Question::first();
        $correctOptions = $question->options()->where('is_correct', true)->get();
        
        $this->assertCount(2, $correctOptions);
        $this->assertTrue($correctOptions->contains('option_key', 'A'));
        $this->assertTrue($correctOptions->contains('option_key', 'C'));
    }
}
