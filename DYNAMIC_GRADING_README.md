# Dynamic Grading System

This document explains the dynamic grading system implemented in GRAIL V3.

## Overview

The dynamic grading system allows teachers to customize how grades are calculated and displayed in the gradebook. It supports multiple grading methods and configurable parameters. Grades update instantly in both Estimated and Projected views and are preserved for export.

## Features

### Grading Methods

1. **Percentage-Based**: Displays grades as percentages (e.g., 85%)
2. **Linear**: Converts percentages to 1.0-5.0 scale using linear interpolation
3. **Curved**: Applies a curved grading scale based on class performance
4. **Pass/Fail**: Simple pass/fail with configurable passing threshold
5. **Custom**: User-defined formulas including:
   - Inverse Linear (95% = 1.0, 75% = 3.0)
   - Exponential Curve
   - Step-based grading

### Configurable Parameters (front-end)

- **Maximum Score (max_score)**: Upper cap for scaling (e.g., 95 caps 100 to 95)
- **Passing Score (passing_score)**: Minimum percent required to pass
- **Passing Grade (passing_grade)**: Grade value when at passing_score (linear/custom)
- **Custom Formula (custom_formula)**: For custom grading method (inverse_linear, exponential, step)

These parameters are saved per class section and loaded automatically.

## How to Use

### 1. Access the Gradebook

Navigate to any class section's gradebook view.

### 2. Select Grading Mode

In the header section, you'll find a "Grading Mode" dropdown with the following options:
- Percentage-Based
- Linear (1.0–5.0)
- Curved (1.0–5.0)
- Pass/Fail (1.0–5.0)
- Custom

### 3. Customize Settings

When you select "Custom" or want to adjust parameters for other methods:
1. Click the "Customize" button that appears
2. A modal will open with configurable parameters
3. Adjust the values as needed
4. See a live preview of how grades will be calculated
5. Click "Apply Settings" to save

### 4. View Results

The gradebook updates immediately using the selected method and parameters. The current settings summary appears beside the Customize button.

Two display modes are available:
- **Estimated Grades**: Normalized average using only completed component weights (e.g., only Attendance+Quiz so far). Missing components are ignored in the denominator.
- **Projected Final**: Uses full configured weights (e.g., Attendance/Quiz/Exam and Term weights). Missing components count as 0.

## Technical Implementation

### Backend Components

- **GradingService**: Core service for grade calculations
- **GradingController**: API endpoints for saving/loading settings
- **ClassSection Model**: Stores grading settings as JSON
- **AssessmentScore::calculatePercentageScore()**: Stores pass‑anchored scaled percentages where the per‑assessment `passing_score` maps to 75% and 0/max map to 0%/100% respectively.
  - Below passing: scaled = 75 × (raw% / passing%)
  - At/above passing: scaled = 75 + ((raw% − passing%) / (100 − passing%)) × 25

### Frontend Components

- **Dynamic JavaScript**: Handles real-time grade conversion (percentage↔linear/custom)
- **Modal Interface**: For parameter customization
- **Estimated/Projected Toggles**: Switches between views and recalculates displays
- **Tooltips/Breakdowns**: Midterm/Final/Overall breakdowns reflect contributions by type/term.

### Database Schema

The `class_sections` table includes a `grading_settings` JSON column that stores:
```json
{
  "grading_method": "linear",
  "max_score": 95,
  "max_grade": 100,
  "passing_score": 75,
  "passing_grade": 3.0,
  "custom_formula": "inverse_linear"
}
```

## API Endpoints

- `POST /grading/calculate` - Calculate individual grade
- `GET /grading/params` - Get default parameters for method
- `POST /subjects/{subject}/classes/{classSection}/grading/settings` - Save settings
- `GET /subjects/{subject}/classes/{classSection}/grading/settings` - Load settings

When an assessment’s `max_score` or `passing_score` is updated, the server recalculates all related `percentage_score` values to keep gradebook and analytics consistent.

## Examples

### Linear Grading
- 95% → 1.0 (excellent)
- 85% → 2.0 (good)
- 75% → 3.0 (passing)
- 65% → 4.0 (failing)

### Custom Inverse Linear
- 95% → 1.0
- 85% → 2.0
- 75% → 3.0
- 65% → 4.0

### Step-based Grading
- 97%+ → 1.00
- 94-96% → 1.25
- 91-93% → 1.50
- 88-90% → 1.75
- 85-87% → 2.00
- 82-84% → 2.25
- 79-81% → 2.50
- 76-78% → 2.75
- 75% → 3.00
- <75% → 5.00

## Benefits

1. **Flexibility**: Teachers can adapt grading to their specific needs
2. **Transparency**: Clear display of current grading method and parameters
3. **Consistency**: Settings are saved per class section and percentages persist in DB
4. **Real-time**: Instant updates when changing methods or parameters
5. **Accurate scenarios**: Estimated vs Projected views match classroom workflows
6. **User-friendly**: Intuitive modal interface for customization

## Future Enhancements

- Grade distribution analysis
- Export grades with different methods
- Bulk grade calculation
- Historical grade tracking
- Advanced statistical curves 