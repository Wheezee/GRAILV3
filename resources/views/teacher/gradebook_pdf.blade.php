<!DOCTYPE html>
<html>
<head>
    <title>Gradebook PDF</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { margin-bottom: 0; }
        p { margin-top: 2px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #333; padding: 4px; font-size: 12px; }
        th { background: #eee; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <h2>Gradebook - {{ $classSection->section }}</h2>
    <p>{{ $classSection->subject->code }} - {{ $classSection->subject->title }}</p>
    <p>
        @if($gradingStructure)
            Weights: Midterm {{ $gradingStructure->midterm_weight }}% | Final {{ $gradingStructure->final_weight }}%
        @else
            Weights: Midterm 50% | Final 50%
        @endif
    </p>
    <table>
        <thead>
            <tr>
                <th rowspan="3">Student</th>
                {{-- Midterm Section Header --}}
                @if($midtermAssessmentTypes->count() > 0)
                    @php
                        $midtermColspan = 0;
                        foreach($midtermAssessmentTypes as $type) {
                            $midtermColspan += $assessments['midterm'][$type->id]['assessments']->count() ?: 1;
                        }
                    @endphp
                    <th colspan="{{ $midtermColspan }}" class="text-center">Midterm</th>
                @endif
                {{-- Final Section Header --}}
                @if($finalAssessmentTypes->count() > 0)
                    @php
                        $finalColspan = 0;
                        foreach($finalAssessmentTypes as $type) {
                            $finalColspan += $assessments['final'][$type->id]['assessments']->count() ?: 1;
                        }
                    @endphp
                    <th colspan="{{ $finalColspan }}" class="text-center">Final</th>
                @endif
                <th rowspan="3" class="text-center">Midterm Grade</th>
                <th rowspan="3" class="text-center">Final Grade</th>
                <th rowspan="3" class="text-center">Overall Grade</th>
            </tr>
            <tr>
                {{-- Midterm Assessment Types --}}
                @foreach($midtermAssessmentTypes as $assessmentType)
                    @php
                        $assessmentCount = $assessments['midterm'][$assessmentType->id]['assessments']->count();
                        $colspan = max($assessmentCount, 1);
                    @endphp
                    <th colspan="{{ $colspan }}" class="text-center">{{ $assessmentType->name }}<br>Weight: {{ $assessmentType->weight }}%</th>
                @endforeach
                {{-- Final Assessment Types --}}
                @foreach($finalAssessmentTypes as $assessmentType)
                    @php
                        $assessmentCount = $assessments['final'][$assessmentType->id]['assessments']->count();
                        $colspan = max($assessmentCount, 1);
                    @endphp
                    <th colspan="{{ $colspan }}" class="text-center">{{ $assessmentType->name }}<br>Weight: {{ $assessmentType->weight }}%</th>
                @endforeach
            </tr>
            <tr>
                {{-- Midterm Assessments --}}
                @foreach($midtermAssessmentTypes as $assessmentType)
                    @php $assessmentList = $assessments['midterm'][$assessmentType->id]['assessments']; @endphp
                    @if($assessmentList->count() > 0)
                        @foreach($assessmentList as $assessment)
                            <th class="text-center">{{ $assessment->name }}<br>Max: {{ $assessment->max_score }}</th>
                        @endforeach
                    @else
                        <th class="text-center">No Assessments</th>
                    @endif
                @endforeach
                {{-- Final Assessments --}}
                @foreach($finalAssessmentTypes as $assessmentType)
                    @php $assessmentList = $assessments['final'][$assessmentType->id]['assessments']; @endphp
                    @if($assessmentList->count() > 0)
                        @foreach($assessmentList as $assessment)
                            <th class="text-center">{{ $assessment->name }}<br>Max: {{ $assessment->max_score }}</th>
                        @endforeach
                    @else
                        <th class="text-center">No Assessments</th>
                    @endif
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
                <tr>
                    <td>{{ $student->last_name }}, {{ $student->first_name }}</td>
                    {{-- Midterm Scores --}}
                    @foreach($midtermAssessmentTypes as $assessmentType)
                        @php $assessmentList = $assessments['midterm'][$assessmentType->id]['assessments']; @endphp
                        @if($assessmentList->count() > 0)
                            @foreach($assessmentList as $assessment)
                                @php
                                    $score = $student->assessmentScores->where('assessment_id', $assessment->id)->first();
                                    $displayScore = $score && $score->score !== null ? $score->score : '--';
                                @endphp
                                <td class="text-center">{{ $displayScore }}</td>
                            @endforeach
                        @else
                            <td class="text-center">--</td>
                        @endif
                    @endforeach
                    {{-- Final Scores --}}
                    @foreach($finalAssessmentTypes as $assessmentType)
                        @php $assessmentList = $assessments['final'][$assessmentType->id]['assessments']; @endphp
                        @if($assessmentList->count() > 0)
                            @foreach($assessmentList as $assessment)
                                @php
                                    $score = $student->assessmentScores->where('assessment_id', $assessment->id)->first();
                                    $displayScore = $score && $score->score !== null ? $score->score : '--';
                                @endphp
                                <td class="text-center">{{ $displayScore }}</td>
                            @endforeach
                        @else
                            <td class="text-center">--</td>
                        @endif
                    @endforeach
                    <td class="text-center font-bold">{{ $student->midterm_grade !== null ? $student->midterm_grade : '-' }}</td>
                    <td class="text-center font-bold">{{ $student->final_grade !== null ? $student->final_grade : '-' }}</td>
                    <td class="text-center font-bold">{{ $student->overall_grade !== null ? $student->overall_grade : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="100%" class="text-center">No students enrolled</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>