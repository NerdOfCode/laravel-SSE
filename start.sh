#!/bin/bash

# Start the SSE standalone server

echo "🚀 Starting SSE Server..."
echo ""
echo "Server will be available at:"
echo "  http://localhost:8000"
echo ""
echo "Available endpoints:"
echo "  /               - HTML client"
echo "  /counter        - Simple counter"
echo "  /progress       - Progress monitor"
echo "  /clock          - Real-time clock"
echo "  /random         - Random numbers"
echo "  /messages       - Message stream"
echo "  /server-stats   - Server statistics"
echo ""
echo "Press Ctrl+C to stop the server"
echo "================================"
echo ""

php -S localhost:8000 server.php
