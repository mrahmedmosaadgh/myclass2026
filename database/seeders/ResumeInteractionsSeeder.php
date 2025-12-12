<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ResumeQuestion;
use App\Models\ResumeAnswer;
use App\Models\ResumeQuestionComment;
use App\Models\ResumeAnswerRating;
use App\Models\ResumeAnswerLike;
use App\Models\ResumeCommentLike;
use App\Models\ResumeAnswerBookmark;

class ResumeInteractionsSeeder extends Seeder
{
    public function run()
    {
        // Get existing users, questions, and answers
        $users = User::take(5)->get();
        $questions = ResumeQuestion::take(3)->get();
        $answers = ResumeAnswer::take(5)->get();

        if ($users->isEmpty() || $questions->isEmpty() || $answers->isEmpty()) {
            $this->command->info('Please ensure you have users, questions, and answers in the database first.');
            return;
        }

        // Create sample comments
        foreach ($answers as $answer) {
            // Create 2-3 comments per answer
            for ($i = 0; $i < rand(2, 3); $i++) {
                $comment = ResumeQuestionComment::create([
                    'user_id' => $users->random()->id,
                    'question_id' => $answer->question_id,
                    'answer_id' => $answer->id,
                    'comment' => 'This is a sample comment #' . ($i + 1) . ' for testing purposes.',
                    'is_public' => true
                ]);

                // Create 1-2 replies for some comments
                if (rand(0, 1)) {
                    ResumeQuestionComment::create([
                        'user_id' => $users->random()->id,
                        'question_id' => $answer->question_id,
                        'answer_id' => $answer->id,
                        'comment' => 'This is a reply to comment #' . ($i + 1),
                        'parent_id' => $comment->id,
                        'is_public' => true
                    ]);
                }

                // Add some comment likes
                foreach ($users->random(rand(1, 3)) as $user) {
                    ResumeCommentLike::create([
                        'user_id' => $user->id,
                        'comment_id' => $comment->id
                    ]);
                }
            }
        }

        // Create sample ratings
        foreach ($answers as $answer) {
            foreach ($users->random(rand(2, 4)) as $user) {
                ResumeAnswerRating::create([
                    'user_id' => $user->id,
                    'answer_id' => $answer->id,
                    'rating' => rand(3, 5),
                    'review_comment' => rand(0, 1) ? 'Great answer! Very helpful.' : null
                ]);
            }
            
            // Update rating statistics
            $answer->updateRatingStats();
        }

        // Create sample likes
        foreach ($answers as $answer) {
            foreach ($users->random(rand(1, 4)) as $user) {
                ResumeAnswerLike::create([
                    'user_id' => $user->id,
                    'answer_id' => $answer->id
                ]);
            }
            
            // Update likes count
            $answer->updateLikesCount();
        }

        // Create sample bookmarks
        foreach ($answers as $answer) {
            foreach ($users->random(rand(1, 2)) as $user) {
                $types = ['favorite', 'important', 'reference'];
                ResumeAnswerBookmark::create([
                    'user_id' => $user->id,
                    'answer_id' => $answer->id,
                    'bookmark_type' => $types[array_rand($types)],
                    'notes' => rand(0, 1) ? 'Bookmarked for future reference' : null
                ]);
            }
        }

        // Update comments count for all answers
        foreach ($answers as $answer) {
            $answer->updateCommentsCount();
        }

        $this->command->info('Resume interactions seeded successfully!');
    }
}
