<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssessmentAnnotation;
use App\Models\Student;
use App\Models\Assessment;

class AnnotationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'assessment_id' => 'nullable|exists:assessments,id',
            'annotation_text' => 'required|string|max:1000',
            'annotation_type' => 'required|in:general,assessment_specific'
        ]);

        $annotation = AssessmentAnnotation::create([
            'student_id' => $request->student_id,
            'assessment_id' => $request->assessment_id,
            'teacher_id' => auth()->id(),
            'annotation_text' => $request->annotation_text,
            'annotation_type' => $request->annotation_type
        ]);

        return response()->json([
            'success' => true,
            'annotation' => [
                'id' => $annotation->id,
                'text' => $annotation->annotation_text,
                'type' => $annotation->annotation_type,
                'assessment_name' => $annotation->assessment ? $annotation->assessment->name : null,
                'created_at' => $annotation->created_at->format('M j, Y'),
                'teacher_name' => $annotation->teacher->name
            ]
        ]);
    }

    public function index(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        
        $annotations = $student->annotations()
            ->with(['assessment', 'teacher'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($annotation) {
                return [
                    'id' => $annotation->id,
                    'text' => $annotation->annotation_text,
                    'type' => $annotation->annotation_type,
                    'assessment_name' => $annotation->assessment ? $annotation->assessment->name : null,
                    'created_at' => $annotation->created_at->format('M j, Y'),
                    'teacher_name' => $annotation->teacher->name
                ];
            });

        return response()->json($annotations);
    }

    public function destroy($id)
    {
        $annotation = AssessmentAnnotation::where('teacher_id', auth()->id())
            ->findOrFail($id);
        
        $annotation->delete();
        
        return response()->json(['success' => true]);
    }
}
