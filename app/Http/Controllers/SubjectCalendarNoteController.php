<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SubjectCalendarNote;
use Illuminate\Http\Request;

class SubjectCalendarNoteController extends Controller
{
    public function index(Request $request, $subjectId)
    {
        $subject = auth()->user()->subjects()->findOrFail($subjectId);

        $month = $request->query('month'); // YYYY-MM
        $query = SubjectCalendarNote::where('subject_id', $subject->id)
            ->where('teacher_id', auth()->id());

        if ($month) {
            $query->whereRaw('strftime("%Y-%m", note_date) = ?', [$month]);
        }

        $notes = $query->get()->map(function ($n) {
            return [
                'date' => $n->note_date->format('Y-m-d'),
                'text' => $n->note_text,
            ];
        });

        return response()->json($notes);
    }

    public function show($subjectId, $date)
    {
        $subject = auth()->user()->subjects()->findOrFail($subjectId);

        $note = SubjectCalendarNote::where('subject_id', $subject->id)
            ->where('teacher_id', auth()->id())
            ->whereDate('note_date', $date)
            ->first();

        return response()->json([
            'date' => $date,
            'text' => $note?->note_text,
            'exists' => $note !== null,
        ]);
    }

    public function upsert(Request $request, $subjectId)
    {
        $request->validate([
            'note_date' => 'required|date',
            'note_text' => 'nullable|string',
        ]);

        $subject = auth()->user()->subjects()->findOrFail($subjectId);

        $note = SubjectCalendarNote::updateOrCreate(
            [
                'subject_id' => $subject->id,
                'teacher_id' => auth()->id(),
                'note_date' => $request->note_date,
            ],
            [
                'note_text' => $request->note_text,
            ]
        );

        return response()->json([
            'success' => true,
            'date' => $note->note_date->format('Y-m-d'),
            'text' => $note->note_text,
        ]);
    }

    public function destroy($subjectId, $date)
    {
        $subject = auth()->user()->subjects()->findOrFail($subjectId);

        SubjectCalendarNote::where('subject_id', $subject->id)
            ->where('teacher_id', auth()->id())
            ->whereDate('note_date', $date)
            ->delete();

        return response()->json(['success' => true]);
    }
}


