<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Exam;
use App\Models\User;
use App\Models\Answer;
use App\Models\StudentExam;
use App\Traits\ManagesModelsTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExamRequest;
use App\Http\Resources\Admin\ExamResource;
use App\Http\Resources\Admin\GradeResource;
use App\Http\Resources\StudentResultResource;
use App\Http\Requests\Admin\StudentExamRequest;
use App\Http\Resources\Admin\ExamQuestionsResource;
use App\Http\Resources\Auth\StudentRegisterResource;

class ExamController extends Controller
{
    use ManagesModelsTrait;

    public function showAll()
  {
      $this->authorize('manage_users');

      $Exams = Exam::with(['students','questions'])->get();
      return response()->json([
          'data' => ExamQuestionsResource::collection($Exams),
          'message' => "Show All Exams Successfully."
      ]);
  }


  public function create(ExamRequest $request)
  {
      $this->authorize('manage_users');
      $question_order = $request->question_order ?? 'regular';

         $Exam =Exam::create ([
            "title" => $request-> title,
            "grade_id" => $request-> grade_id,
            "course_id" => $request-> course_id,
            "test_id" => $request-> test_id,
            "lesson_id" => $request-> lesson_id,
            'creationDate' => now()->timezone('Africa/Cairo')
            ->format('Y-m-d h:i:s'),
            "duration" => $request-> duration,
            "deadLineExam" => $request-> deadLineExam,
            'question_order' => $question_order,

          ]);

           $numOfQuestions = $Exam->questions()->count();

          $Exam->numOfQ = $numOfQuestions;
          $Exam->save();


         $course = $Exam->course;
         $course->numOfExams = $course->exams()->count();
         $course->save();
         return response()->json([
          'data' =>new ExamResource($Exam),
          'message' => "Exam Created Successfully."
      ]);

      }




// public function create(ExamRequest $request): JsonResponse
// {
//     $this->authorize('manage_users');
//     $numOfQuestions = count($request->questions);
//     $Exam = Exam::create([
//         'title' => $request->title,
//         'creationDate' => Carbon::now('Africa/Cairo')->format('Y-m-d H:i:s'),
//         'duration' => $request->duration,
//         'deadLineExam' => $request->deadLineExam,
//         'grade_id' => $request->grade_id,
//         'course_id' => $request->course_id,
//         'lesson_id' => $request->lesson_id,
//         'test_id' => $request->test_id,
//         'numOfQ' => $numOfQuestions,
//         // 'questions' => $request->questions,
//          'questions'  => json_encode($request->questions),

//     ]);

//          $course = $Exam->course;
//          $course->numOfExams = $course->exams()->count();
//          $course->save();

//     return response()->json([
//         'message' => 'Exam created successfully',
//         'data' => $Exam,
//     ]);
// }

      public function assignStudentsToExam(StudentExamRequest $request)
      {
          $this->authorize('manage_users');

          $Exam = Exam::with('students')->find($request->exam_id);
          if (!$Exam) {
              return response()->json([
                  'message' => 'Exam not found'
              ]);
          }
          $Exam->students()->sync($request->student_ids);

          return response()->json([
              'data' => new ExamResource($Exam),
              "message" => "Students added to Exam successfully"
          ]);
      }


  public function edit(string $id)
  {
      $this->authorize('manage_users');
      $Exam = Exam::with(['students','questions'])->find($id);

      if (!$Exam) {
          return response()->json([
              'message' => "Exam not found."
          ]);
      }

      return response()->json([
          'data' =>new ExamResource($Exam),
          'message' => "Edit Exam By ID Successfully."
      ]);
  }




public function showExamQuestions($examId)
{
    $this->authorize('manage_users');

    $exam = Exam::with('questions')
        ->findOrFail($examId);
    // $actualQuestionCount = $exam->questions()->count(); // أو يمكنك استخدام $exam->questions()->whereNull('deleted_at')->count(); إذا كنت تريد أن تكون أكثر وضوحًا

    return response()->json([
        'data' => new ExamQuestionsResource($exam),
        // 'actual_question_count' => $actualQuestionCount,
        'message' => "Show Exam With Questions By Id Successfully."
    ]);
}

  public function showExamResults($examId, $studentId)
  {
      $this->authorize('manage_users');

      $student = User::find($studentId);
      if (!$student) {
          return response()->json([
              'message' => 'الطالب غير موجود.'
          ]);
      }

      $studentExam = StudentExam::where('exam_id', $examId)
          ->where('user_id', $studentId)
          ->first();

      if (!$studentExam) {
          return response()->json([
              'message' => 'لم يتم العثور على بيانات الامتحان للطالب.'
          ]);
      }


      if (!$studentExam->started_at) {
          $studentExam->started_at = now();
      }

      $answers = Answer::with('question.exam')
          ->where('exam_id', $examId)
          ->where('user_id', $studentId)
          ->get();

      if ($answers->isEmpty()) {
          return response()->json([
              'message' => 'لا توجد إجابات لهذا الامتحان.'
          ]);
      }


      $correctAnswers = 0;
      $totalQuestions = $answers->count();
      $exam = null;
      $answersDetail = [];

      foreach ($answers as $answer) {
          $question = $answer->question;
          $is_correct = $question->correct_choice === $answer->selected_choice;

          if ($is_correct) {
              $correctAnswers++;
          }

          if (!$exam) {
              $exam = new ExamResource($question->exam);
          }

          $answersDetail[] = [
              'question_id' => $question->id,
              'question_text' => $question->question,
              'choices' => [
                  'choice_1' => $question->choice_1,
                  'choice_2' => $question->choice_2,
                  'choice_3' => $question->choice_3,
                  'choice_4' => $question->choice_4,
              ],
              'correct_choice' => $question->correct_choice,
              'student_choice' => $answer->selected_choice,
              'is_correct' => $is_correct,
          ];
      }


      $score = ($correctAnswers / $totalQuestions) * 100;

      if (!$studentExam->submitted_at) {
          $studentExam->submitted_at = now();
      }

      $startedAt = Carbon::parse($studentExam->started_at);
$submittedAt = Carbon::parse($studentExam->submitted_at);


$timeTaken = $submittedAt->diff($startedAt)->format('%H:%I:%S');

$studentExam->time_taken = $timeTaken;

      $studentExam->save();

      $studentResource = new StudentRegisterResource($student);

      return response()->json([
          'exam' => $exam,
          'student' => $studentResource,
          'data' => $answersDetail,
          'score' => $score,
          'correctAnswers' => $correctAnswers,
          'started_at' => $studentExam->started_at,
          'submitted_at' => $studentExam->submitted_at,
          'time_taken' => $timeTaken,
          'message' => 'تم عرض نتائج الامتحان بنجاح.',
      ]);
  }


  public function update(ExamRequest $request, string $id)
  {
      $this->authorize('manage_users');
      $question_order = $request->question_order ?? 'regular';
     $Exam =Exam::findOrFail($id);


     if (!$Exam) {
      return response()->json([
          'message' => "Exam not found."
      ]);
  }

     $Exam->update([
        "title" => $request-> title,
        "grade_id" => $request-> grade_id,
        "course_id" => $request-> course_id,
        "test_id" => $request-> test_id,
        "lesson_id" => $request-> lesson_id,
        "creationDate"=> $request->creationDate,
        "duration" => $request-> duration,
        "deadLineExam" => $request-> deadLineExam,
        'question_order' => $question_order,
      ]);

      $numOfQuestions = $Exam->questions()->count();

          $Exam->numOfQ = $numOfQuestions;
          $Exam->save();

         $course = $Exam->course;
         $course->numOfExams = $course->exams()->count();
         $course->save();
     return response()->json([
      'data' =>new ExamResource($Exam),
      'message' => "Update Exam By Id Successfully."
  ]);

}

  public function destroy(string $id)
  {
    $this->authorize('manage_users');
    $exam = Exam::findOrFail($id);
    $course = $exam->course;

    $exam->delete();

    $course->numOfExams = $course->exams()->count();
    $course->save();
    return response()->json([
        'message' => 'Exam Soft Delete Successfully.',
        'data' =>new ExamResource($exam),
        'actual_exam_count' => $course->numOfExams,
    ]);
  }

  public function showDeleted(){
    $this->authorize('manage_users');
$exams=Exam::onlyTrashed()->get();
return response()->json([
    'data' =>ExamResource::collection($exams),
    'message' => "Show Deleted Exams Successfully."
]);
}

public function restore(string $id)
{
$this->authorize('manage_users');
$Exam = Exam::onlyTrashed()->findOrFail($id);
$Exam->restore();

$course = $Exam->course;
$course->numOfExams = $course->exams()->count();
$course->save();

return response()->json([
    'data' =>new ExamResource($Exam),
    'actual_exam_count' => $course->numOfExams,
    'message' => "Restore Exam By Id Successfully."
]);
}

  public function forceDelete(string $id)
  {
    $this->authorize('manage_users');
    $Exam = Exam::withTrashed()->findOrFail($id);
    $course = $Exam->course;

    $Exam->forceDelete();

    $course->numOfExams = $course->exams()->count();
    $course->save();
    return response()->json([
        'message' => " Force Delete Exam By Id Successfully.",
        'actual_exam_count' => $course->numOfExams,
    ]);
  }

  public function getStudentExamResults($studentId, $courseId)
{
    $this->authorize('manage_users');
    $student = User::find($studentId);

    if (!$student) {
        return response()->json([
            'message' => 'الطالب غير موجود.'
        ]);
    }


$student = User::with(['exams' => function ($query) use ($courseId) {
    $query->where('course_id', $courseId);
}])->findOrFail($studentId);

$fourExams = $student->exams->take(4);

$fourExamResults = $fourExams->map(function ($exam) {
    return [
        'exam_id' => $exam->id,
        'title' => $exam->title,
        'score' => $exam->pivot->has_attempted ? $exam->pivot->score : 'absent',
        'has_attempted' => $exam->pivot->has_attempted,
        'started_at' => $exam->pivot->started_at,
        'submitted_at' => $exam->pivot->submitted_at,
        'time_taken' => $exam->pivot->time_taken,
        'correctAnswers' => $exam->pivot->correctAnswers
    ];
})->toArray();

$finalExam = $student->exams->last();
$finalExamResult = [
    'exam_id' => $finalExam->id,
    'title' => $finalExam->title,
    'score' => $finalExam->pivot->has_attempted ? $finalExam->pivot->score : 'absent',
    'has_attempted' => $finalExam->pivot->has_attempted,
    'started_at' => $finalExam->pivot->started_at,
    'submitted_at' => $finalExam->pivot->submitted_at,
    'time_taken' => $finalExam->pivot->time_taken,
    'correctAnswers' => $finalExam->pivot->correctAnswers
];
$totalScore = 0;
$attemptedCount = 0;

foreach ($fourExams as $exam) {
    if ($exam->pivot->has_attempted) {
        $totalScore += $exam->pivot->score;
        $attemptedCount++;
    }
}


$totalPercentageForFourExams = ($totalScore / (4 * 100)) * 100;
$overallTotalScore = $totalScore + ($finalExam->pivot->has_attempted ?
 $finalExam->pivot->score : 0);
$totalExamsCount = $attemptedCount +
 ($finalExam->pivot->has_attempted ? 1 : 0);

$overallTotalPercentage = ($overallTotalScore / (5 * 100)) * 100;
return response()->json([
    'student' => new StudentResultResource($student),
    'four_exam_results' => $fourExamResults,
    'total_percentage_for_four_exams' => round($totalPercentageForFourExams, 2),
    'final_exam_result' => $finalExamResult,
    'overall_total_percentage' => round($overallTotalPercentage, 2),
]);

}




public function getStudent4ExamsResult($studentId, $courseId)
{
    $this->authorize('manage_users');
    $student = User::with(['grade', 'parent'])->findOrFail($studentId);


    $fourExams = $student->exams()
    ->where('course_id', $courseId)
    ->take(4)
    ->get();

    $fourExamResults = $fourExams->map(function ($exam) {
        $score = $exam->pivot->score;
        $hasAttempted = $exam->pivot->has_attempted;
        $started_at = $exam->pivot->started_at;
        $submitted_at = $exam->pivot->submitted_at;
        $time_taken = $exam->pivot->time_taken;
        $correctAnswers = $exam->pivot->correctAnswers;


        $resultScore = ($score === null && $hasAttempted == 0) ? 'absent' : ($hasAttempted ? $score : 'absent');

        return [
            'exam_id' => $exam->id,
            'title' => $exam->title,
            'score' => $resultScore,
            'has_attempted' => $hasAttempted,
            'started_at' => $started_at,
            'submitted_at' => $submitted_at,
            'time_taken' => $time_taken,
            'correctAnswers' => $correctAnswers,
        ];
    });

    $totalScore = 0;
    $attemptedCount = 0;

    foreach ($fourExams as $exam) {
        if ($exam->pivot->has_attempted) {
            $totalScore += $exam->pivot->score ?? 0;
            $attemptedCount++;
        }
    }

    $totalPercentageForFourExams = ($totalScore / (4 * 100)) * 100;

    return response()->json([
        'student' => new StudentRegisterResource($student),
        'four_exam_results' => $fourExamResults,
        'total_percentage_for_four_exams' => round($totalPercentageForFourExams, 2),
    ]);

}

public function getStudentOverallResults($studentId)
{
    $this->authorize('manage_users');
    $student = User::findOrFail($studentId);

    $totalOverallScore = 0;
    $totalMaxScore = 0;

    $courses = $student->courses()->with('exams')->get();

    foreach ($courses as $course) {
        foreach ($course->exams as $exam) {

            $studentExam = $exam->students()
            ->where('user_id', $studentId)
            ->first();

            if ($studentExam && !is_null($studentExam->pivot->score)) {
                $totalOverallScore += $studentExam->pivot->score;
            }
            $totalMaxScore += 100;
        }
    }


    $overallScorePercentage = ($totalMaxScore > 0) ? ($totalOverallScore / $totalMaxScore) * 100 : 0;
    return response()->json([
        'student' => [
            'id' => $student->id,
            'name' => $student->name,
            'email' => $student->email,
            'img' => $student->img,
            'grade' => new GradeResource($student->grade),
        ],
        'overall_score_percentage' => round($overallScorePercentage, 2),
    ]);
}






}
