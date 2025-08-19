from flask import Flask, jsonify, request
from flask_cors import CORS
import socket

app = Flask(__name__)
CORS(app)


@app.route('/api/host-ip', methods=['GET'])
def host_ip():
    """Return the host machine's LAN IP address.
    Uses a UDP socket to infer the outbound interface IP.
    """
    ip_address = '127.0.0.1'
    try:
        sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        sock.connect(("8.8.8.8", 80))
        ip_address = sock.getsockname()[0]
        sock.close()
    except Exception:
        try:
            ip_address = request.host.split(':')[0]
        except Exception:
            pass

    return jsonify({
        'success': True,
        'ip': ip_address
    }), 200


if __name__ == '__main__':
    # Run on a dedicated port so it does not conflict with other services
    app.run(debug=True, host='0.0.0.0', port=5055)


