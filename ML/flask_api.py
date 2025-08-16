from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import numpy as np

app = Flask(__name__)
CORS(app)  # Enable CORS for cross-origin requests

# Load the model
model = joblib.load('grail_rf_model.pkl')

# Risk labels mapping
risk_labels = ['risk_at_risk', 'risk_chronic_procrastinator', 'risk_incomplete']

@app.route('/api/predict', methods=['POST'])
def predict():
    """
    API endpoint for risk prediction
    Expected JSON payload:
    {
        "avg_score_pct": 68,
        "variation_score_pct": 35,
        "late_submission_pct": 40,
        "missed_submission_pct": 10
    }
    """
    try:
        # Get JSON data from request
        data = request.get_json()
        
        # Validate required fields
        required_fields = ['avg_score_pct', 'variation_score_pct', 'late_submission_pct', 'missed_submission_pct']
        for field in required_fields:
            if field not in data:
                return jsonify({
                    'success': False,
                    'error': f'Missing required field: {field}'
                }), 400
        
        # Extract features
        features = [
            float(data['avg_score_pct']),
            float(data['variation_score_pct']),
            float(data['late_submission_pct']),
            float(data['missed_submission_pct'])
        ]
        
        # Validate input ranges
        for i, value in enumerate(features):
            if not 0 <= value <= 100:
                field_names = ['avg_score_pct', 'variation_score_pct', 'late_submission_pct', 'missed_submission_pct']
                return jsonify({
                    'success': False,
                    'error': f'{field_names[i]} must be between 0 and 100'
                }), 400
        
        # Make prediction
        input_data = np.array([features])
        prediction = model.predict(input_data)[0]
        
        # Get predicted risk categories
        results = [label for label, is_risk in zip(risk_labels, prediction) if is_risk == 1]
        
        # Format response
        if results:
            risk_details = []
            for risk in results:
                if risk == 'risk_at_risk':
                    risk_details.append({
                        'code': 'risk_at_risk',
                        'label': 'At Risk',
                        'description': 'Student shows signs of being at risk'
                    })
                elif risk == 'risk_chronic_procrastinator':
                    risk_details.append({
                        'code': 'risk_chronic_procrastinator',
                        'label': 'Chronic Procrastinator',
                        'description': 'Student frequently delays assignments'
                    })
                elif risk == 'risk_incomplete':
                    risk_details.append({
                        'code': 'risk_incomplete',
                        'label': 'Incomplete Work',
                        'description': 'Student has incomplete assignments'
                    })
            
            response = {
                'success': True,
                'has_risks': True,
                'risks': risk_details,
                'risk_count': len(results),
                'input_data': {
                    'avg_score_pct': features[0],
                    'variation_score_pct': features[1],
                    'late_submission_pct': features[2],
                    'missed_submission_pct': features[3]
                }
            }
        else:
            response = {
                'success': True,
                'has_risks': False,
                'risks': [],
                'risk_count': 0,
                'message': 'No risks detected',
                'input_data': {
                    'avg_score_pct': features[0],
                    'variation_score_pct': features[1],
                    'late_submission_pct': features[2],
                    'missed_submission_pct': features[3]
                }
            }
        
        return jsonify(response), 200
        
    except ValueError as e:
        return jsonify({
            'success': False,
            'error': 'Invalid numeric values provided'
        }), 400
    except Exception as e:
        return jsonify({
            'success': False,
            'error': f'Prediction failed: {str(e)}'
        }), 500

@app.route('/api/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'success': True,
        'status': 'healthy',
        'service': 'GRAIL Risk Predictor API',
        'version': '1.0.0'
    }), 200

@app.route('/api/info', methods=['GET'])
def api_info():
    """API information endpoint"""
    return jsonify({
        'success': True,
        'service': 'GRAIL Risk Predictor API',
        'version': '1.0.0',
        'endpoints': {
            'predict': {
                'method': 'POST',
                'url': '/api/predict',
                'description': 'Predict student risks based on performance metrics'
            },
            'health': {
                'method': 'GET',
                'url': '/api/health',
                'description': 'Health check endpoint'
            }
        },
        'required_fields': [
            'avg_score_pct',
            'variation_score_pct', 
            'late_submission_pct',
            'missed_submission_pct'
        ],
        'field_ranges': {
            'min': 0,
            'max': 100
        }
    }), 200

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000) 