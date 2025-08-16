import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.multioutput import MultiOutputClassifier
from sklearn.metrics import classification_report
import joblib

# Load dataset
df = pd.read_csv("grail.csv")

# Separate features and targets
X = df[[
    'avg_score_pct',
    'variation_score_pct',
    'late_submission_pct',
    'missed_submission_pct'
]]

# Targets: multi-label binary columns
y = df[[
    'risk_at_risk',
    'risk_chronic_procrastinator',
    'risk_incomplete',
    'risk_inconsistent_performer'
]]

# Train-test split
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Create and train the model
rf = RandomForestClassifier(n_estimators=100, random_state=42)
model = MultiOutputClassifier(rf)
model.fit(X_train, y_train)

# Predict and evaluate
y_pred = model.predict(X_test)
print("Classification Report:\n")
print(classification_report(y_test, y_pred, target_names=y.columns))

# Save model
joblib.dump(model, "grail_rf_model.pkl")
print("Model saved as 'grail_rf_model.pkl'")
