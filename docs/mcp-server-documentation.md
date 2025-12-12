# MCP Server Documentation

## Overview
This document provides comprehensive documentation for the MCP (Model Context Protocol) Server built with Node.js and Express.js. The server provides basic functionality for health checks and time retrieval.

## Project Structure
```
MCP Server/
├── server.js          # Main server file
├── package.json       # Node.js dependencies and scripts
├── package-lock.json  # Dependency lock file
└── node_modules/      # Installed dependencies
```

## Installation and Setup

### Prerequisites
- Node.js (v14 or higher)
- npm (Node Package Manager)

### Installation Steps
1. Navigate to the MCP Server directory:
   ```bash
   cd "MCP Server"
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

## Running the Server

### Development Mode
```bash
npm run dev
```
(Requires nodemon to be installed globally: `npm install -g nodemon`)

### Production Mode
```bash
npm start
```
or
```bash
node server.js
```

## Server Configuration
- **Port**: 3000
- **Base URL**: http://localhost:3000
- **Console Message**: "MCP Server listening at http://localhost:3000"

## API Endpoints

### 1. Health Check Endpoint
**GET** `/`

Returns a simple text response confirming the server is running.

**Request:**
```bash
curl http://localhost:3000/
```

**Response:**
```
MCP Server is running!
```

**Status Code:** 200 OK

### 2. Get Current Time Endpoint
**POST** `/get-time`

Returns the current server time in a structured JSON format.

**Request:**
```bash
curl -X POST http://localhost:3000/get-time \
  -H "Content-Type: application/json" \
  -d '{}'
```

**Response:**
```json
{
  "displayText": "Server time is: 7/17/2025, 11:55:40 AM",
  "details": "Full timestamp: 2025-07-17T08:55:40.843Z"
}
```

**Status Code:** 200 OK

**Console Output:**
```
Received a request for /get-time
```

## Code Structure

### server.js
The main server file contains:

1. **Express Setup**
   - Creates Express application instance
   - Configures JSON body parsing middleware

2. **Routes**
   - GET `/`: Health check endpoint
   - POST `/get-time`: Time retrieval endpoint

3. **Server Initialization**
   - Starts server on port 3000
   - Logs startup confirmation message

### Dependencies
- **express**: Web framework for Node.js
- **nodemon** (dev): Development server with auto-restart

## Usage Examples

### Testing with curl

#### Health Check
```bash
curl http://localhost:3000/
```

#### Get Current Time
```bash
curl -X POST http://localhost:3000/get-time \
  -H "Content-Type: application/json" \
  -d '{"requestId": "12345"}'
```

### Testing with JavaScript (fetch)

```javascript
// Health check
fetch('http://localhost:3000/')
  .then(res => res.text())
  .then(console.log);

// Get time
fetch('http://localhost:3000/get-time', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({})
})
  .then(res => res.json())
  .then(console.log);
```

## Troubleshooting

### Common Issues

1. **Port Already in Use**
   - Error: `EADDRINUSE :::3000`
   - Solution: Change port in server.js or kill process using port 3000

2. **Module Not Found**
   - Error: `Cannot find module 'express'`
   - Solution: Run `npm install` in the MCP Server directory

3. **Permission Denied**
   - Error: `EACCES`
   - Solution: Ensure proper file permissions or run as administrator

### Debug Mode
To enable detailed logging, you can modify server.js to include:
```javascript
app.use((req, res, next) => {
  console.log(`${new Date().toISOString()} - ${req.method} ${req.path}`);
  next();
});
```

## Extension Ideas

The server can be extended with additional endpoints:

1. **GET /status**: Detailed server status
2. **POST /echo**: Echo back request data
3. **GET /health**: Detailed health metrics
4. **WebSocket support**: Real-time communication

## Security Considerations

For production deployment:
1. Add input validation
2. Implement rate limiting
3. Add CORS configuration
4. Use environment variables for configuration
5. Add authentication if needed

## Version Information
- **Server Version**: 1.0.0
- **Node.js**: Compatible with v14+
- **Express.js**: v4.18.2