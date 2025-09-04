<?php

namespace Tests\Unit;

use App\Models\Assessment;
use App\Models\AssessmentScore;
use PHPUnit\Framework\TestCase;

class AssessmentScoreScalingTest extends TestCase
{
    public function test_maps_passing_score_to_75_percent()
    {
        $assessment = new Assessment([
            'max_score' => 15,
            'passing_score' => 5,
        ]);

        $score = new AssessmentScore([
            'score' => 5,
        ]);
        $score->setRelation('assessment', $assessment);

        $score->calculatePercentageScore();

        $this->assertEquals(75.00, $score->percentage_score);
    }

    public function test_scales_below_passing_linearly_to_75()
    {
        $assessment = new Assessment([
            'max_score' => 15,
            'passing_score' => 5,
        ]);

        $score = new AssessmentScore([
            'score' => 2.5, // 50% of passing score => should be 37.5%
        ]);
        $score->setRelation('assessment', $assessment);

        $score->calculatePercentageScore();

        $this->assertEquals(37.5, $score->percentage_score);
    }

    public function test_scales_above_passing_up_to_100()
    {
        $assessment = new Assessment([
            'max_score' => 15,
            'passing_score' => 5,
        ]);

        $score = new AssessmentScore([
            'score' => 15,
        ]);
        $score->setRelation('assessment', $assessment);

        $score->calculatePercentageScore();

        $this->assertEquals(100.00, $score->percentage_score);
    }
}


