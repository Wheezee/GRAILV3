import pandas as pd
import joblib

# Load the model
model = joblib.load('grail_rf_model.pkl')

# Example student data (you can replace with dynamic input later)
student = pd.DataFrame([{
    'avg_score_pct': 68,
    'variation_score_pct': 35,
    'late_submission_pct': 40,
    'missed_submission_pct': 10
}])

# Predict risks
prediction = model.predict(student)[0]

# Label mapping
risk_labels = ['risk_at_risk', 'risk_chronic_procrastinator', 'risk_incomplete']
results = [label for label, is_risk in zip(risk_labels, prediction) if is_risk == 1]

print("🔎 Predicted Risk Categories:", results if results else ["✅ Not At Risk"])
