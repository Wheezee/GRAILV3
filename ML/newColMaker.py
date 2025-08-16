import pandas as pd

# Load the dataset
df = pd.read_csv("grail.csv")

# Add the new column based on the specified rule
df['risk_inconsistent_performer'] = df['variation_score_pct'].apply(lambda x: 1 if 45 <= x <= 55 else 0)

# Save the updated CSV (overwrite in place)
df.to_csv("grail.csv", index=False)

print("Added 'risk_inconsistent_performer' column to grail.csv.")