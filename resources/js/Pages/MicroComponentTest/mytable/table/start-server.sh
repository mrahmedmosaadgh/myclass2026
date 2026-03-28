#!/bin/bash

# Simple server script for the offline table app
echo "🚀 Starting Offline Table App Server..."
echo "📍 Location: $(pwd)"
echo ""

# Check if Python 3 is available
if command -v python3 &> /dev/null; then
    echo "✅ Using Python 3 server"
    echo "🌐 Open: http://localhost:8000"
    echo "📱 For mobile testing, use your local IP address"
    echo ""
    echo "Press Ctrl+C to stop the server"
    echo ""
    python3 -m http.server 8000
elif command -v python &> /dev/null; then
    echo "✅ Using Python 2 server"
    echo "🌐 Open: http://localhost:8000"
    echo ""
    echo "Press Ctrl+C to stop the server"
    echo ""
    python -m SimpleHTTPServer 8000
elif command -v node &> /dev/null; then
    echo "✅ Using Node.js server"
    echo "🌐 Open: http://localhost:8000"
    echo ""
    echo "Press Ctrl+C to stop the server"
    echo ""
    npx http-server -p 8000
else
    echo "❌ No suitable server found"
    echo "Please install Python 3 or Node.js"
    echo ""
    echo "For Python 3:"
    echo "  brew install python3"
    echo ""
    echo "For Node.js:"
    echo "  brew install node"
    exit 1
fi
