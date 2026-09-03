#!/bin/bash
# MediLife Portal - HTTPS Demo Startup Script
#
# Usage:
#   ./run_https.sh [--cert path/to/cert.crt] [--key path/to/key.key] [--port 443]
#
# This script starts the Flask application with HTTPS for demo purposes.
# For production, use a proper WSGI server (gunicorn/uwsgi) with reverse proxy.

set -e

# Default values
CERT_FILE="../certs/server.crt"
KEY_FILE="../certs/server.key"
PORT=443

# Parse arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --cert)
            CERT_FILE="$2"
            shift 2
            ;;
        --key)
            KEY_FILE="$2"
            shift 2
            ;;
        --port)
            PORT="$2"
            shift 2
            ;;
        --help)
            echo "Usage: $0 [--cert path] [--key path] [--port port]"
            echo ""
            echo "Options:"
            echo "  --cert    Path to SSL certificate (default: ../certs/server.crt)"
            echo "  --key     Path to SSL key (default: ../certs/server.key)"
            echo "  --port    Port number (default: 443)"
            echo ""
            echo "Example:"
            echo "  $0 --cert ../certs/server.crt --key ../certs/server.key --port 8443"
            exit 0
            ;;
        *)
            echo "Unknown option: $1"
            exit 1
            ;;
    esac
done

# Check if certificate files exist
if [ ! -f "$CERT_FILE" ]; then
    echo "Error: Certificate file not found: $CERT_FILE"
    echo "Please generate certificates first (see ../certs/README.md)"
    exit 1
fi

if [ ! -f "$KEY_FILE" ]; then
    echo "Error: Key file not found: $KEY_FILE"
    exit 1
fi

# Check if Python dependencies are installed
if ! python3 -c "import flask" 2>/dev/null; then
    echo "Error: Flask not installed. Please install dependencies:"
    echo "  pip install -r requirements.txt"
    exit 1
fi

echo "=============================================="
echo "  MediLife Portal - HTTPS Demo Server"
echo "=============================================="
echo ""
echo "Configuration:"
echo "  Certificate: $CERT_FILE"
echo "  Key:         $KEY_FILE"
echo "  Port:        $PORT"
echo ""
echo "Starting server..."
echo ""
echo "Access the application at:"
echo "  https://localhost:$PORT"
echo ""
echo "Default credentials (if bootstrap was run):"
echo "  Admin:     admin / (password set during bootstrap)"
echo "  Doctor:    dr.smith / Doctor123!"
echo "  Reception: reception / Reception123!"
echo "  Patient:   patient1 / Patient123!"
echo ""
echo "Press Ctrl+C to stop the server"
echo "=============================================="
echo ""

# Run the server
cd "$(dirname "$0")"
python3 manage.py runssl --cert "$CERT_FILE" --key "$KEY_FILE" --port "$PORT"
