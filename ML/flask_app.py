from flask import Flask, request
import joblib
import numpy as np

app = Flask(__name__)
model = joblib.load('grail_rf_model.pkl')

# Risk labels mapping
risk_labels = ['risk_at_risk', 'risk_chronic_procrastinator', 'risk_incomplete']

@app.route('/', methods=['GET'])
def form():
    return '''
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>GRAIL Risk Predictor</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { background-color: #f8f9fa; }
                .container { max-width: 600px; }
                .card { box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
            </style>
        </head>
        <body>
            <div class="container mt-5">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0 text-center">🎓 GRAIL Risk Predictor</h2>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="avg_score_pct" class="form-label">Average Score (%)</label>
                                <input type="number" class="form-control" id="avg_score_pct" name="avg_score_pct" 
                                       step="any" min="0" max="100" required>
                            </div>
                            <div class="mb-3">
                                <label for="variation_score_pct" class="form-label">Variation in Score (%)</label>
                                <input type="number" class="form-control" id="variation_score_pct" name="variation_score_pct" 
                                       step="any" min="0" max="100" required>
                            </div>
                            <div class="mb-3">
                                <label for="late_submission_pct" class="form-label">Late Submission Rate (%)</label>
                                <input type="number" class="form-control" id="late_submission_pct" name="late_submission_pct" 
                                       step="any" min="0" max="100" required>
                            </div>
                            <div class="mb-3">
                                <label for="missed_submission_pct" class="form-label">Missed Submission Rate (%)</label>
                                <input type="number" class="form-control" id="missed_submission_pct" name="missed_submission_pct" 
                                       step="any" min="0" max="100" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">🔍 Predict Risk</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
    '''

@app.route('/', methods=['POST'])
def predict():
    try:
        features = [
            float(request.form['avg_score_pct']),
            float(request.form['variation_score_pct']),
            float(request.form['late_submission_pct']),
            float(request.form['missed_submission_pct'])
        ]
        input_data = np.array([features])
        prediction = model.predict(input_data)[0]
        
        # Get predicted risk categories
        results = [label for label, is_risk in zip(risk_labels, prediction) if is_risk == 1]
        
        # Format the results for display
        if results:
            risk_display = []
            for risk in results:
                if risk == 'risk_at_risk':
                    risk_display.append('⚠️ At Risk')
                elif risk == 'risk_chronic_procrastinator':
                    risk_display.append('⏰ Chronic Procrastinator')
                elif risk == 'risk_incomplete':
                    risk_display.append('📝 Incomplete Work')
            result_text = '<br>'.join(risk_display)
            alert_class = 'alert-warning'
            icon = '⚠️'
        else:
            result_text = '✅ No Risk Detected'
            alert_class = 'alert-success'
            icon = '✅'

        return f'''
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>GRAIL Risk Predictor - Results</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body {{ background-color: #f8f9fa; }}
                    .container {{ max-width: 600px; }}
                    .card {{ box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }}
                </style>
            </head>
            <body>
                <div class="container mt-5">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h2 class="mb-0 text-center">{icon} Prediction Results</h2>
                        </div>
                        <div class="card-body">
                            <div class="alert {alert_class} text-center" role="alert">
                                <h4 class="alert-heading">{icon} Risk Assessment</h4>
                                <p class="mb-0 fs-5">{result_text}</p>
                            </div>
                            <div class="d-grid">
                                <a href="/" class="btn btn-outline-primary">🔄 Try Another Prediction</a>
                            </div>
                        </div>
                    </div>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            </body>
            </html>
        '''
    except Exception as e:
        return f'''
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>GRAIL Risk Predictor - Error</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body {{ background-color: #f8f9fa; }}
                    .container {{ max-width: 600px; }}
                    .card {{ box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }}
                </style>
            </head>
            <body>
                <div class="container mt-5">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h2 class="mb-0 text-center">❌ Error</h2>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-danger" role="alert">
                                <h4 class="alert-heading">Something went wrong!</h4>
                                <p class="mb-0">Error: {e}</p>
                            </div>
                            <div class="d-grid">
                                <a href="/" class="btn btn-outline-danger">🔙 Go Back</a>
                            </div>
                        </div>
                    </div>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            </body>
            </html>
        '''

if __name__ == '__main__':
    app.run(debug=True)
