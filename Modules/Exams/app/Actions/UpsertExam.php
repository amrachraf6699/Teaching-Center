<?php

namespace Modules\Exams\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Exams\Models\Exam;

class UpsertExam
{
    public function handle(array $attributes, array $questions, ?Exam $exam = null): Exam
    {
        return DB::transaction(function () use ($attributes, $questions, $exam): Exam {
            $exam ??= new Exam();
            $exam->fill($attributes);
            $exam->save();

            $keptQuestionIds = [];

            foreach (array_values($questions) as $index => $question) {
                $storedQuestion = $exam->questions()->updateOrCreate(
                    [
                        'id' => $question['id'] ?? null,
                    ],
                    [
                        'type' => $question['type'],
                        'prompt' => $question['prompt'],
                        'points' => $question['points'],
                        'position' => $index + 1,
                    ],
                );

                $keptQuestionIds[] = $storedQuestion->id;
                $keptOptionIds = [];

                foreach (collect($question['options'])->values() as $optionIndex => $option) {
                    $storedOption = $storedQuestion->options()->updateOrCreate(
                        [
                            'id' => $option['id'] ?? null,
                        ],
                        [
                            'label' => $option['label'],
                            'is_correct' => (bool) $option['is_correct'],
                            'position' => $optionIndex + 1,
                        ],
                    );

                    $keptOptionIds[] = $storedOption->id;
                }

                $storedQuestion->options()
                    ->whereNotIn('id', $keptOptionIds)
                    ->delete();
            }

            $exam->questions()
                ->whereNotIn('id', $keptQuestionIds)
                ->delete();

            $exam->refresh();
            $exam->recalculateMaxScore();
            $exam->save();

            return $exam->load('questions.options');
        });
    }
}
