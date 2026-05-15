<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Evaluation;
use App\Models\EvaluationQuestion;
use App\Enum\QuestionType;
use Illuminate\Http\Request;
use App\Utils\HttpResponseCode;
use Illuminate\Support\Facades\DB;

class EvaluationQuestionController extends Controller
{
    public function index($eventId)
    {
        $event = Event::findOrFail($eventId);
        $questions = $event->evaluationQuestions()
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();

        return $this->success("Questions fetched successfully", $questions);
    }

    public function store(Request $request, $eventId)
    {
        $textValue = QuestionType::TEXT->value;
        $ratingValue = QuestionType::RATING->value;
        $multipleChoiceValue = QuestionType::MULTIPLE_CHOICE->value;

        $request->validate([
            'type' => 'required|in:' . implode(',', [$ratingValue, $textValue, $multipleChoiceValue]) . ',header',

            'question_text' => 'required_if:type,' . $textValue . ',' . $ratingValue . '|nullable|string',
            'options' => 'required_if:type,' . $multipleChoiceValue . '|nullable|array|min:2',
            'options.*' => 'required_if:type,' . $multipleChoiceValue . '|nullable|string|max:255',

            // untuk header
            'header_title' => 'required_if:type,header|nullable|string|max:255',
            'header_description' => 'nullable|string',

            // common fields
            'is_required' => 'boolean',
            'page_number' => 'nullable|integer|min:1',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $event = Event::findOrFail($eventId);

        $nextSortOrder = array_key_exists('sort_order', $request->all())
            ? (int) $request->sort_order
            : ((int) $event->evaluationQuestions()->max('sort_order')) + 1;

        $question = $event->evaluationQuestions()->create([
            'question_text' => $request->type === 'header' ? '' : $request->question_text,
            'type' => $request->type,
            'options' => $request->type === 'multiple_choice' ? $request->options : null,
            'is_required' => $request->is_required ?? true,

            'page_number' => $request->page_number ?? 1,
            'sort_order' => $nextSortOrder,

            'header_title' => $request->type === 'header' ? $request->header_title : null,
            'header_description' => $request->type === 'header' ? $request->header_description : null,
        ]);

        return $this->success("Question created successfully", $question, HttpResponseCode::HTTP_CREATED);
    }

    public function update(Request $request, $eventId, $id)
    {
        $request->validate([
            'type' => 'required|in:rating,text,multiple_choice,header',

            'question_text' => 'required_if:type,text,rating|nullable|string',
            'options' => 'required_if:type,multiple_choice|nullable|array|min:2',
            'options.*' => 'required_if:type,multiple_choice|string|max:255',

            // untuk header
            'header_title' => 'required_if:type,header|nullable|string|max:255',
            'header_description' => 'nullable|string',

            // common fields
            'is_required' => 'boolean',
            'page_number' => 'nullable|integer|min:1',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $question = EvaluationQuestion::where('event_id', $eventId)->findOrFail($id);

        $question->update([
            'question_text' => $request->type === 'header' ? '' : $request->question_text,
            'type' => $request->type,
            'options' => $request->type === 'multiple_choice' ? $request->options : null,
            'is_required' => $request->is_required ?? true,

            'page_number' => $request->page_number ?? 1,
            'sort_order' => $request->sort_order ?? 0,

            'header_title' => $request->type === 'header' ? $request->header_title : null,
            'header_description' => $request->type === 'header' ? $request->header_description : null,
        ]);

        return $this->success("Question updated successfully", $question);
    }

    public function destroy($eventId, $id)
    {
        $question = EvaluationQuestion::where('event_id', $eventId)->findOrFail($id);
        $question->delete();

        return $this->success("Question deleted successfully");
    }

    public function reorder(Request $request, $eventId)
    {
        $request->validate([
            'question_ids' => 'required|array|min:1',
            'question_ids.*' => 'required|exists:evaluation_questions,id',
        ]);

        Event::findOrFail($eventId);
        $questionIds = $request->question_ids;
        $validCount = EvaluationQuestion::where('event_id', $eventId)
            ->whereIn('id', $questionIds)
            ->count();

        if ($validCount !== count($questionIds)) {
            return $this->error("Some questions do not belong to this event.", HttpResponseCode::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::transaction(function () use ($eventId, $questionIds) {
            foreach ($questionIds as $index => $questionId) {
                EvaluationQuestion::where('event_id', $eventId)
                    ->where('id', $questionId)
                    ->update(['sort_order' => $index + 1]);
            }
        });

        $questions = EvaluationQuestion::where('event_id', $eventId)
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();

        return $this->success("Question order updated successfully", $questions);
    }

    public function importQuestions(Request $request, $eventId)
    {
        $request->validate([
            'source_event_id' => 'required|exists:events,id',
        ]);

        $targetEvent = Event::findOrFail($eventId);
        $sourceEvent = Event::findOrFail($request->source_event_id);

        if ($targetEvent->id === $sourceEvent->id) {
            return $this->error("Source event and target event must be different.", HttpResponseCode::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();
        try {
            $sourceQuestions = $sourceEvent->evaluationQuestions()
                ->orderBy('sort_order')
                ->orderBy('created_at')
                ->get();
            $clonedQuestions = [];
            $nextSortOrder = ((int) $targetEvent->evaluationQuestions()->max('sort_order')) + 1;

            foreach ($sourceQuestions as $index => $q) {
                $clone = $q->replicate();
                $clone->event_id = $targetEvent->id;
                $clone->sort_order = $nextSortOrder + $index;
                $clone->save();
                $clonedQuestions[] = $clone;
            }

            DB::commit();

            return $this->success("Questions imported successfully", $clonedQuestions);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("Failed to import questions: " . $e->getMessage(), HttpResponseCode::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function responses($eventId)
    {
        $event = Event::findOrFail($eventId);
        $questions = $event->evaluationQuestions()
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();

        $evaluations = Evaluation::with([
                'eventRegistration.user',
                'evaluationAnswers.evaluationQuestion',
            ])
            ->whereHas('eventRegistration', function ($query) use ($eventId) {
                $query->where('event_id', $eventId);
            })
            ->latest()
            ->get();

        $questionStats = $questions->map(function ($question) use ($evaluations) {
            $answers = $evaluations
                ->flatMap->evaluationAnswers
                ->where('evaluation_question_id', $question->id)
                ->pluck('answer_value')
                ->filter(fn ($value) => $value !== null && $value !== '');

            $stats = [
                'count' => $answers->count(),
                'average' => null,
                'distribution' => [],
            ];

            if ($question->type->value === 'rating') {
                $numericAnswers = $answers->map(fn ($value) => (int) $value);
                $stats['average'] = $numericAnswers->count() > 0
                    ? round($numericAnswers->avg(), 2)
                    : null;
                $stats['distribution'] = collect(range(1, 5))
                    ->mapWithKeys(fn ($value) => [$value => $numericAnswers->filter(fn ($answer) => $answer === $value)->count()])
                    ->all();
            }

            if ($question->type->value === 'multiple_choice') {
                $stats['distribution'] = collect($question->options ?? [])
                    ->mapWithKeys(fn ($option) => [$option => $answers->filter(fn ($answer) => $answer === $option)->count()])
                    ->all();
            }

            return [
                'question_id' => $question->id,
                'question_text' => $question->question_text,
                'type' => $question->type->value,
                'stats' => $stats,
            ];
        });

        $rows = $evaluations->map(function ($evaluation) {
            return [
                'id' => $evaluation->id,
                'submitted_at' => optional($evaluation->created_at)->toISOString(),
                'registration_id' => $evaluation->event_registration_id,
                'user' => [
                    'name' => $evaluation->eventRegistration?->user?->name,
                    'email' => $evaluation->eventRegistration?->user?->email,
                    'nrp' => $evaluation->eventRegistration?->user?->nrp,
                ],
                'answers' => $evaluation->evaluationAnswers->mapWithKeys(function ($answer) {
                    return [
                        $answer->evaluation_question_id => [
                            'question_text' => $answer->evaluationQuestion?->question_text,
                            'type' => $answer->evaluationQuestion?->type?->value,
                            'value' => $answer->answer_value,
                        ],
                    ];
                })->all(),
            ];
        });

        return $this->success("Evaluation responses fetched successfully", [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
            ],
            'questions' => $questions,
            'summary' => $questionStats,
            'responses' => $rows,
        ]);
    }
}
