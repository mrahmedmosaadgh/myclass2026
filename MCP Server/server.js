const express = require('express');
const app = express();
const port = 3000;

// Middleware to parse JSON request bodies
app.use(express.json());

// Root endpoint for health check
app.get('/', (req, res) => {
  res.send('MCP Server is running!');
});

// POST endpoint to get current server time
app.post('/get-time', (req, res) => {
  console.log('Received a request for /get-time');
  
  const currentTime = new Date();
  const formattedTime = currentTime.toLocaleString();
  const isoTimestamp = currentTime.toISOString();
  
  const response = {
    displayText: `Server time is: ${formattedTime}`,
    details: `Full timestamp: ${isoTimestamp}`
  };
  
  res.json(response);
});

// Start the server
app.listen(port, () => {
  console.log(`MCP Server listening at http://localhost:${port}`);
});