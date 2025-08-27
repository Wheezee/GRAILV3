# GRAIL (Grading and Risk Assessment through Intelligent Learning)

GRAIL is a smart grading system designed to help educators identify and support at-risk students early. It leverages machine learning to analyze student performance patterns and flag potential issues such as chronic lateness, missed submissions, or academic underperformance.

---

## Features

- Custom assessment types (not limited to quizzes, exams, etc.)
- Gradebook with percentage-based grading
- Performance graph visualization
- Machine learning risk detection (multi-label classification)
- Instructor-only authentication and access
- Predicts multiple risk categories per student (e.g., "At Risk", "Chronic Procrastinator")
- Flags students for early intervention based on performance trends

---

## Core Machine Learning Features (Holup, still cooking)

- `avg_score_pct` – average performance across all assessments
- `variation_score_pct` – consistency in scores
- `late_submission_pct` – percentage of late tasks (optional)
- `missed_submission_pct` – percentage of tasks completely missed

---

## Tech Stack

- Laravel – backend framework
- TailwindCSS – frontend styling
- Python + Scikit-learn – machine learning
- Docker – deployment-ready containers

---

## Disclaimer

GRAIL is not an LMS. It is a teacher-focused grading tool with early risk detection features — no student portal, no content delivery.

---

## License

This project is licensed under the MIT License.