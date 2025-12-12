# MCP Server with Kilo Code Integration Guide

## Overview
This guide explains how to use the MCP server with Kilo Code, including how to set it up as an MCP (Model Context Protocol) server for AI assistants and development workflows.

## What is MCP?
MCP (Model Context Protocol) is a protocol that enables AI assistants to interact with external tools and services. The server we created can serve as a simple MCP server providing time-related functionality.

## Setting Up MCP Server for Kilo Code

### 1. Start the MCP Server
```bash
cd "MCP Server"
npm start
```

The server will start on `http://localhost:3000` and display:
```
MCP Server listening at http://localhost:3000
```

### 2. Using with Kilo Code Tools

#### Direct API Calls from Kilo Code
You can use the MCP server endpoints directly with Kilo Code's tools:

**Health Check:**
```javascript
// Use execute_command tool
execute_command: curl http://localhost:3000/
```

**Get Current Time:**
```javascript
// Use execute_command tool
execute_command: curl -X POST http://localhost:3000/get-time -H "Content-Type: application/json" -d '{}'
```

### 3. Integration Patterns

#### Pattern 1: Time-Aware Development
Use the MCP server to get accurate timestamps for:
- Build timestamps
- Log entries
- File versioning
- Deployment tracking

**Example Kilo Code usage:**
```javascript
// Get current time for build process
const response = await fetch('http://localhost:3000/get-time', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ purpose: 'build-timestamp' })
});
const timeData = await response.json();
console.log(`Build started at: ${timeData.displayText}`);
```

#### Pattern 2: Health Monitoring
Use the health endpoint to monitor server status:

**Kilo Code monitoring script:**
```javascript
// Check server health
const health = await fetch('http://localhost:3000/').then(r => r.text());
if (health === 'MCP Server is running!') {
  console.log('✅ MCP Server is healthy');
} else {
  console.log('❌ MCP Server is down');
}
```

### 4. Kilo Code Workflow Integration

#### Development Workflow
1. **Start Server**: Use Kilo Code's execute_command to start the MCP server
2. **Health Check**: Verify server is running before operations
3. **Time Stamping**: Use /get-time for accurate timestamps
4. **Logging**: Log operations with server timestamps

#### Example Kilo Code Session
```bash
# Start MCP server
execute_command: cd "MCP Server" && npm start

# In another terminal, test endpoints
execute_command: curl http://localhost:3000/
execute_command: curl -X POST http://localhost:3000/get-time
```

### 5. Advanced Kilo Code Usage

#### Creating Custom MCP Tools
Extend the server to provide more MCP-compatible endpoints:

**Example: Add file info endpoint**
```javascript
// Add to server.js
app.post('/file-info', (req, res) => {
  const { filename } = req.body;
  const stats = fs.statSync(filename);
  res.json({
    size: stats.size,
    modified: stats.mtime,
    created: stats.birthtime
  });
});
```

#### Using with Kilo Code's MCP Tools
When Kilo Code supports MCP servers, you can:

1. **Register the server** as an MCP tool
2. **Use server endpoints** directly in prompts
3. **Chain operations** with server responses

### 6. Debugging with Kilo Code

#### Server Logs
View server logs in real-time:
```bash
execute_command: cd "MCP Server" && node server.js
```

#### Test Endpoints
Quick endpoint testing:
```bash
# Health check
execute_command: curl -s http://localhost:3000/

# Time endpoint
execute_command: curl -s -X POST http://localhost:3000/get-time | jq .
```

### 7. Kilo Code MCP Server Commands

#### Quick Commands
```bash
# Start server
npm start

# Development with auto-restart
npm run dev

# Test health
curl http://localhost:3000/

# Get time
curl -X POST http://localhost:3000/get-time
```

#### Integration Script
Create a Kilo Code-compatible script:
```javascript
// mcp-client.js
const MCP_SERVER_URL = 'http://localhost:3000';

async function getServerTime() {
  const response = await fetch(`${MCP_SERVER_URL}/get-time`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({})
  });
  return response.json();
}

async function checkHealth() {
  const response = await fetch(`${MCP_SERVER_URL}/`);
  return response.text();
}

module.exports = { getServerTime, checkHealth };
```

### 8. Best Practices

#### 1. Always Check Health First
```javascript
const health = await checkHealth();
if (health !== 'MCP Server is running!') {
  throw new Error('MCP Server not available');
}
```

#### 2. Handle Server Errors
```javascript
try {
  const timeData = await getServerTime();
  return timeData.displayText;
} catch (error) {
  console.error('MCP Server error:', error);
  return new Date().toLocaleString(); // Fallback
}
```

#### 3. Use in Build Scripts
```javascript
// package.json
{
  "scripts": {
    "build": "node -e \"require('./mcp-client.js').getServerTime().then(t => console.log('Build started:', t.displayText))\" && npm run build:actual"
  }
}
```

## Quick Start with Kilo Code

1. **Start the server:**
   ```bash
   cd "MCP Server" && npm start
   ```

2. **Test connection:**
   ```bash
   curl http://localhost:3000/
   ```

3. **Use in development:**
   ```bash
   curl -X POST http://localhost:3000/get-time
   ```

## Troubleshooting

### Server Not Starting
```bash
# Check if port 3000 is in use
execute_command: netstat -ano | findstr :3000

# Kill process on port 3000 (Windows)
execute_command: taskkill /PID <PID> /F
```

### Connection Issues
```bash
# Test connectivity
execute_command: curl -v http://localhost:3000/

# Check server logs
execute_command: cd "MCP Server" && node server.js
```

## Next Steps
1. Extend the server with more MCP-compatible endpoints
2. Add authentication for production use
3. Implement WebSocket support for real-time updates
4. Create Kilo Code-specific MCP client libraries