import pandas as pd
import random

def generate_dummy_data(num_samples=300):
    data = []
    for _ in range(num_samples):
        avg_score_pct = random.randint(40, 100)
        variation_score_pct = random.randint(0, 50)
        late_submission_pct = random.choice([0, 10, 20, 30, 40])
        missed_submission_pct = random.choice([0, 10, 20, 30])

        # Multi-label risk tags
        at_risk = 1 if avg_score_pct < 75 or missed_submission_pct >= 20 else 0
        chronic_procrastinator = 1 if late_submission_pct >= 30 else 0
        incomplete = 1 if missed_submission_pct >= 20 else 0

        data.append({
            'avg_score_pct': avg_score_pct,
            'variation_score_pct': variation_score_pct,
            'late_submission_pct': late_submission_pct,
            'missed_submission_pct': missed_submission_pct,
            'risk_at_risk': at_risk,
            'risk_chronic_procrastinator': chronic_procrastinator,
            'risk_incomplete': incomplete
        })

    return pd.DataFrame(data)

df = generate_dummy_data()
df.to_csv('grail.csv', index=False)
print("✅ Dummy training data with multi-labels saved to training_data.csv")
