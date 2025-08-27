# Optional Quiz Feature for Assessments

This feature allows teachers to convert any assessment into an interactive quiz that students can take online. Quiz scores are automatically recorded in the assessment scores table.

## Features

- **Optional Quiz Conversion**: Any assessment can be converted to a quiz
- **Multiple Question Types**: Multiple choice, identification, and true/false questions
- **Token-Based Access**: Students access quizzes using unique 8-character tokens
- **QR Code Generation**: Easy access via QR codes
- **Automatic Grading**: Scores are automatically calculated and recorded
- **Real-time Progress**: Students see their progress as they take the quiz
- **Mobile Friendly**: Responsive design works on all devices

## How to Use

### For Teachers

1. **Create an Assessment**: First create a regular assessment in GRAILV3
2. **Convert to Quiz**: Click the "Make Quiz" button (question mark icon) next to the assessment
3. **Add Questions**: 
   - Choose question type (Multiple Choice, Identification, True/False)
   - Enter question text
   - For multiple choice: Add options and specify correct answer
   - For identification/true-false: Enter correct answer
   - Set points for each question
4. **Generate Tokens**: After creating the quiz, generate unique tokens for each student
5. **Share Access**: Share the QR code or URL with students along with their individual tokens

### For Students

1. **Access Quiz**: Scan QR code or visit the quiz URL
2. **Enter Token**: Input the 8-character token provided by the teacher
3. **Take Quiz**: Answer questions one by one with progress tracking
4. **Submit**: Review answers and submit the quiz
5. **View Results**: See immediate score and percentage

## Question Types

### Multiple Choice
- Teacher provides multiple options
- Students select one correct answer
- Options are displayed as radio buttons

### Identification
- Students type their answer in a text field
- Case-insensitive matching
- Partial matching supported

### True/False
- Students select True or False
- Simple binary choice

## Technical Details

### Database Changes
- `assessments` table: Added `is_quiz`, `unique_url`, `qr_code_enabled`, `auto_grade` fields
- `assessment_questions` table: Stores quiz questions with options and correct answers
- `assessment_tokens` table: Manages student access tokens

### Routes
- Teacher routes (protected by auth):
  - `GET /assessments/{id}/quiz` - Quiz creation form
  - `POST /assessments/{id}/quiz` - Save quiz questions
  - `GET /assessments/{id}/quiz/tokens` - View tokens and QR codes
  - `POST /assessments/{id}/quiz/tokens/generate` - Generate student tokens
  - `PUT /tokens/{id}/regenerate` - Regenerate individual token

- Student routes (public):
  - `GET /assessment/{url}/access` - Quiz access form
  - `POST /assessment/{url}/validate-token` - Validate student token
  - `GET /assessment/{url}/take` - Take the quiz
  - `POST /assessment/{url}/submit` - Submit quiz answers
  - `GET /assessment/{url}/result` - View quiz results

### Security Features
- Token-based authentication for students
- One-time use tokens (marked as used after submission)
- Teacher ownership verification for all operations
- Session-based quiz state management

## Files Added/Modified

### New Files
- `app/Models/AssessmentQuestion.php`
- `app/Models/AssessmentToken.php`
- `app/Http/Controllers/StudentAssessmentController.php`
- `resources/views/teacher/assessments/quiz-form.blade.php`
- `resources/views/teacher/assessments/quiz-tokens.blade.php`
- `resources/views/student/assessment-access.blade.php`
- `resources/views/student/take-assessment.blade.php`
- `resources/views/student/assessment-result.blade.php`

### Modified Files
- `app/Models/Assessment.php` - Added quiz relationships and fields
- `app/Http/Controllers/AssessmentController.php` - Added quiz management methods
- `routes/web.php` - Added quiz routes
- `composer.json` - Added QR code package dependency

### Database Migrations
- `2025_08_06_000000_add_quiz_fields_to_assessments_table.php`
- `2025_08_06_000001_create_assessment_questions_table.php`
- `2025_08_06_000002_create_assessment_tokens_table.php`

## Dependencies Added
- `simplesoftwareio/simple-qrcode` - For QR code generation

## Usage Example

1. Teacher creates "Midterm Exam" assessment
2. Clicks "Make Quiz" button
3. Adds 10 multiple choice questions
4. Generates tokens for 25 students
5. Projects QR code in classroom
6. Students scan QR code, enter their token, and take quiz
7. Scores automatically appear in gradebook

## Benefits

- **Reduced Paper Usage**: No need to print quizzes
- **Instant Grading**: Automatic scoring saves time
- **Better Analytics**: Digital data for performance analysis
- **Flexible Access**: Students can take quizzes from anywhere
- **Secure**: Token-based access prevents unauthorized access
- **Scalable**: Works for any class size
