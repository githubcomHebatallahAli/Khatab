<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseWithLessonsExamsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
    return [
        'course' => new CourseResource($this),
        'lessons' => $this->lessons->map(function ($lesson) {
            return [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'poster' => $lesson->poster,
                'video' => $lesson->video,
                'duration' => $lesson->duration,
                'ExplainPdf' => $lesson->ExplainPdf,
                'numOfPdf' => $lesson->numOfPdf,
                'description' => $lesson->description,
                'grade' => new GradeResource($lesson->grade),
                'lec' => new MainResource($lesson->lec),
                'exam' => $lesson->exam ? [
                    'id' => $lesson->exam->id,
                    'title' => $lesson->exam->title,
                    'duration' => $lesson->exam->duration,
                    'creationDate' => $lesson->exam->creationDate,
                    'numOfQ' => $lesson->exam->numOfQ,
                     'question_order' => $lesson->exam-> question_order,
                    'formatted_deadLineExam' => $lesson->exam->formatted_deadLineExam,
                    'questions' => $lesson->exam->questions->map(function ($question) {
                        return [
                            'id' => $question->id,
                            'question' => $question->question,
                            'choices' => [
                                'choice_1' => $question->choice_1,
                                'choice_2' => $question->choice_2,
                                'choice_3' => $question->choice_3,
                                'choice_4' => $question->choice_4,
                            ],
                            'correct_choice' => $question->correct_choice,
                        ];
                    }),
                ] : null,
            ];
        }),
        // إضافة الامتحان غير المرتبط في حال كان موجودًا
        'final_exam' => $this->exams()->whereNull('lesson_id')->first() ? [
            'id' => $this->exams()->whereNull('lesson_id')->first()->id,
            'title' => $this->exams()->whereNull('lesson_id')->first()->title,
            'duration' => $this->exams()->whereNull('lesson_id')->first()->duration,
            'creationDate' => $this->exams()->whereNull('lesson_id')->first()->creationDate,
            'numOfQ' => $this->exams()->whereNull('lesson_id')->first()->numOfQ,
            'question_order' => $this->exams()->whereNull('lesson_id')->first()->question_order,
            'formatted_deadLineExam' => $this->exams()->whereNull('lesson_id')->first()->formatted_deadLineExam,
            'questions' => $this->exams()->whereNull('lesson_id')->first()->questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question' => $question->question,
                    'choices' => [
                        'choice_1' => $question->choice_1,
                        'choice_2' => $question->choice_2,
                        'choice_3' => $question->choice_3,
                        'choice_4' => $question->choice_4,
                    ],
                    'correct_choice' => $question->correct_choice,
                ];
            }),
        ] : null,
    ];
}


}
