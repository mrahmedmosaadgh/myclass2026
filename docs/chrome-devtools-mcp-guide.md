# Chrome DevTools MCP Server - Complete Guide

**Date:** March 19, 2026  
**Repository:** https://github.com/ChromeDevTools/chrome-devtools-mcp  
**Purpose:** Enable AI assistants to control Chrome DevTools programmatically  
**Status:** ✅ **INSTALLED & CONFIGURED**

---

## 🎉 Installation Complete!

The Chrome DevTools MCP server has been successfully installed and tested on your system.

### Quick Start (After Installation)

**Easiest Method - Use the Setup Script:**
```powershell
cd C:\my_project\myclass2026-main\chrome-devtools-mcp
.\start-all.bat
```

This will:
1. ✅ Start Chrome with remote debugging
2. ✅ Open Presentation Builder V2
3. ✅ Verify the connection
4. ✅ Prepare the MCP server

**Manual Method:**
1. Start Chrome: `.\start-chrome.bat`
2. Start MCP: `.\start-mcp.bat`
3. Test: `node test-presentation-builder.js`

### What's Available

- **Location:** `C:\my_project\myclass2026-main\chrome-devtools-mcp`
- **Test Script:** `test-presentation-builder.js`
- **Quick Start Guide:** `QUICK_START.md`
- **Batch Files:** `start-all.bat`, `start-chrome.bat`, `start-mcp.bat`

---

## Table of Contents

1. [Overview](#overview)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Usage Examples](#usage-examples)
5. [Available Tools](#available-tools)
6. [Integration with Laravel + Vue Projects](#integration-with-laravel--vue-projects)
7. [Troubleshooting](#troubleshooting)
8. [Advanced Features](#advanced-features)

---

## Overview

The Chrome DevTools MCP (Model Context Protocol) server is a powerful tool that allows AI assistants and other MCP clients to interact with Chrome DevTools programmatically. This enables automated debugging, page inspection, performance analysis, and much more.

### Key Capabilities

- **DOM Inspection:** Query and manipulate the DOM
- **JavaScript Execution:** Run JavaScript in the browser context
- **Console Access:** Read console logs and errors
- **Network Monitoring:** Inspect network requests and responses
- **Performance Analysis:** Measure page performance metrics
- **Debugging:** Set breakpoints, step through code
- **Accessibility Testing:** Check accessibility issues
- **Screenshots:** Capture page screenshots

### Use Cases for Laravel + Vue Developers

1. **Debug Vue Components:** Inspect component state and props
2. **Test API Calls:** Monitor Laravel API requests from Vue
3. **Performance Optimization:** Analyze page load times
4. **Automated Testing:** Create AI-assisted test scenarios
5. **Live Debugging:** Real-time inspection during development

---

## Installation

### Prerequisites

- **Node.js:** Version 18 or higher
- **Chrome/Chromium:** Latest version
- **npm or yarn:** Package manager

### Step-by-Step Installation

#### 1. Clone the Repository

```bash
git clone https://github.com/ChromeDevTools/chrome-devtools-mcp.git
cd chrome-devtools-mcp
```

#### 2. Install Dependencies

```bash
npm install
```

#### 3. Build the Project (if required)

```bash
npm run build
```

---

## Configuration

### Start Chrome with Remote Debugging

The Chrome DevTools MCP requires Chrome to be running with remote debugging enabled.

#### Windows

```powershell
# Basic remote debugging
chrome.exe --remote-debugging-port=9222

# With custom user data directory (recommended)
chrome.exe --remote-debugging-port=9222 --user-data-dir="C:\chrome-debug-profile"

# Full example with your Laravel app
Start-Process "chrome.exe" -ArgumentList "--remote-debugging-port=9222", "--user-data-dir=`"C:\chrome-debug`"", "http://127.0.0.1:8000"
```

#### macOS

```bash
# Basic
/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome --remote-debugging-port=9222 --user-data-dir="/tmp/chrome-debug-profile"

# Or with Chrome Canary
/Applications/Google\ Chrome\ Canary.app/Contents/MacOS/Google\ Chrome\ Canary --remote-debugging-port=9222
```

#### Linux

```bash
google-chrome --remote-debugging-port=9222 --user-data-dir="/tmp/chrome-debug-profile"
```

### Start the MCP Server

#### Option 1: Direct Start

```bash
npm start -- --port=9222
```

#### Option 2: Using npx (No Installation)

```bash
npx chrome-devtools-mcp --port=9222
```

#### Option 3: Global Installation

```bash
npm install -g chrome-devtools-mcp
chrome-devtools-mcp --port=9222
```

---

## MCP Client Configuration

### For MCP-Enabled IDEs

Add to your MCP configuration file (`.mcp.json`, `mcp-settings.json`, or IDE settings):

#### Configuration Example 1: Stdio Transport

```json
{
  "mcpServers": {
    "chrome-devtools": {
      "command": "node",
      "args": ["/path/to/chrome-devtools-mcp/dist/index.js"],
      "env": {
        "DEBUG_PORT": "9222"
      }
    }
  }
}
```

#### Configuration Example 2: HTTP Transport

```json
{
  "mcpServers": {
    "chrome-devtools": {
      "type": "http",
      "url": "http://localhost:3000/chrome-devtools",
      "env": {
        "CHROME_REMOTE_DEBUGGING_PORT": "9222"
      }
    }
  }
}
```

#### Configuration Example 3: Simple Setup

```json
{
  "mcpServers": {
    "chrome": {
      "command": "npx",
      "args": ["chrome-devtools-mcp"],
      "env": {
        "CHROME_REMOTE_DEBUGGING_PORT": "9222"
      }
    }
  }
}
```

### For Lingma / Custom AI Assistants

Create a workflow configuration:

```json
{
  "workflows": {
    "chrome-debug": {
      "tools": ["chrome-devtools"],
      "capabilities": [
        "page_inspection",
        "javascript_execution",
        "console_access",
        "network_monitoring"
      ]
    }
  }
}
```

---

## Usage Examples

### Basic Commands

#### Navigate to a Page

```javascript
await chrome_devtools.navigate('http://127.0.0.1:8000/classroom-records/presentation/builder-v2');
```

#### Get Page Title

```javascript
const title = await chrome_devtools.evaluate(() => document.title);
console.log('Page title:', title);
```

#### Get Current URL

```javascript
const url = await chrome_devtools.evaluate(() => window.location.href);
```

### DOM Inspection

#### Query Element

```javascript
const element = await chrome_devtools.evaluate(() => {
  return {
    tag: document.querySelector('.presentation-builder-v2').tagName,
    text: document.querySelector('.presentation-builder-v2').textContent
  };
});
```

#### Get All Slides

```javascript
const slides = await chrome_devtools.evaluate(() => {
  const slideThumbs = document.querySelectorAll('.slide-thumb');
  return Array.from(slideThumbs).map(slide => ({
    index: slide.textContent,
    active: slide.classList.contains('active')
  }));
});
```

#### Inspect Vue Component

```javascript
const vueData = await chrome_devtools.evaluate(() => {
  const app = document.querySelector('#app');
  if (app && app.__vue_app__) {
    return {
      componentName: app.__vue_app__._instance.type.name,
      props: app.__vue_app__._instance.props,
      data: app.__vue_app__._instance.data
    };
  }
  return null;
});
```

### JavaScript Execution

#### Run Custom Script

```javascript
const result = await chrome_devtools.evaluate(() => {
  // Your JavaScript code here
  const slides = document.querySelectorAll('.slide-thumb');
  return {
    totalSlides: slides.length,
    activeSlide: document.querySelector('.slide-thumb.active')?.textContent
  };
});
```

#### Trigger Button Click

```javascript
await chrome_devtools.evaluate(() => {
  document.querySelector('button[onclick*="Export JSON"]').click();
});
```

### Console Access

#### Get Console Messages

```javascript
const logs = await chrome_devtools.getConsoleMessages();
console.log('Console logs:', logs);
```

#### Listen for Console Events

```javascript
await chrome_devtools.on('console', (message) => {
  console.log('New console message:', message);
});
```

### Network Monitoring

#### Get Network Requests

```javascript
const requests = await chrome_devtools.getNetworkRequests();
console.log('Network requests:', requests);
```

#### Monitor API Calls

```javascript
const apiCalls = await chrome_devtools.evaluate(() => {
  const performance = window.performance.getEntriesByType('resource');
  return performance
    .filter(entry => entry.name.includes('/api/'))
    .map(entry => ({
      url: entry.name,
      type: entry.initiator.type,
      duration: entry.duration
    }));
});
```

### Screenshots

#### Capture Full Page

```javascript
const screenshot = await chrome_devtools.screenshot();
// Returns base64 encoded image
```

#### Capture Specific Element

```javascript
const elementScreenshot = await chrome_devtools.screenshot({
  selector: '.slide-container'
});
```

---

## Integration with Laravel + Vue Projects

### Debug Vue.js Components

#### Example 1: Inspect Presentation Builder State

```javascript
// Get current presentation data
const presentationState = await chrome_devtools.evaluate(() => {
  const app = document.querySelector('#app').__vue_app__;
  const vm = app._instance;
  
  return {
    currentSlideIndex: vm.currentSlideIndex,
    totalSlides: vm.slides.length,
    mode: vm.mode,
    isOnline: vm.isOnline
  };
});

console.log('Presentation State:', presentationState);
```

#### Example 2: Check Slide Elements

```javascript
const slideData = await chrome_devtools.evaluate(() => {
  const app = document.querySelector('#app').__vue_app__;
  const vm = app._instance;
  const currentSlide = vm.slides[vm.currentSlideIndex];
  
  return {
    slideId: currentSlide.id,
    elementCount: currentSlide.elements.length,
    elements: currentSlide.elements.map(el => ({
      id: el.id,
      type: el.type,
      x: el.x,
      y: el.y
    }))
  };
});
```

### Test JSON Import/Export

#### Simulate File Import

```javascript
// Test import functionality
const testImport = await chrome_devtools.evaluate(() => {
  const testSlides = [
    {
      id: 'test-slide-1',
      elements: [
        {
          id: 'text1',
          type: 'text',
          content: 'Test Content',
          x: 100,
          y: 50,
          width: 300,
          height: 50
        }
      ]
    }
  ];
  
  // Simulate the import
  const vm = document.querySelector('#app').__vue_app__._instance;
  vm.slides = testSlides;
  vm.currentSlideIndex = 0;
  
  return { success: true, importedCount: testSlides.length };
});
```

#### Monitor Export Process

```javascript
// Watch for export events
await chrome_devtools.evaluate(() => {
  const originalExport = document.querySelector('button[onclick*="Export JSON"]').onclick;
  
  document.querySelector('button[onclick*="Export JSON"]').onclick = function(e) {
    console.log('Export button clicked');
    // Call original handler
    if (originalExport) originalExport.call(this, e);
  };
});
```

### Debug Laravel API Calls

#### Monitor Classroom Records API

```javascript
const apiCalls = await chrome_devtools.evaluate(() => {
  const xhr = new XMLHttpRequest();
  const requests = [];
  
  const originalOpen = XMLHttpRequest.prototype.open;
  const originalSend = XMLHttpRequest.prototype.send;
  
  XMLHttpRequest.prototype.open = function(method, url) {
    this._method = method;
    this._url = url;
    originalOpen.apply(this, arguments);
  };
  
  XMLHttpRequest.prototype.send = function(data) {
    requests.push({
      method: this._method,
      url: this._url,
      timestamp: Date.now()
    });
    originalSend.apply(this, arguments);
  };
  
  return requests;
});
```

### Performance Testing

#### Measure Page Load Time

```javascript
const performance = await chrome_devtools.evaluate(() => {
  const timing = window.performance.timing;
  return {
    pageLoadTime: timing.loadEventEnd - timing.navigationStart,
    domReadyTime: timing.domComplete - timing.domContentLoadedEventStart,
    serverResponseTime: timing.responseEnd - timing.requestStart
  };
});
```

#### Analyze Component Render Performance

```javascript
const renderMetrics = await chrome_devtools.evaluate(() => {
  const slides = document.querySelectorAll('.slide-thumb');
  return {
    totalElements: document.querySelectorAll('*').length,
    slideCount: slides.length,
    domNodes: document.documentElement.innerHTML.length
  };
});
```

---

## Available Tools

### Page Control

| Tool | Description | Example |
|------|-------------|---------|
| `navigate(url)` | Navigate to URL | `navigate('http://localhost:8000')` |
| `reload()` | Reload current page | `reload()` |
| `goBack()` | Go back in history | `goBack()` |
| `goForward()` | Go forward in history | `goForward()` |

### DOM Manipulation

| Tool | Description | Example |
|------|-------------|---------|
| `evaluate(fn)` | Execute JavaScript | `evaluate(() => document.title)` |
| `querySelector(selector)` | Find element | `querySelector('.slide')` |
| `querySelectorAll(selector)` | Find all matching | `querySelectorAll('button')` |
| `getInnerHTML(selector)` | Get element HTML | `getInnerHTML('#app')` |

### Console & Debugging

| Tool | Description | Example |
|------|-------------|---------|
| `getConsoleMessages()` | Get console logs | `getConsoleMessages()` |
| `setBreakpoint(url, line)` | Set breakpoint | `setBreakpoint('app.js', 42)` |
| `resume()` | Resume execution | `resume()` |
| `stepOver()` | Step over | `stepOver()` |

### Network

| Tool | Description | Example |
|------|-------------|---------|
| `getNetworkRequests()` | Get all requests | `getNetworkRequests()` |
| `clearNetworkLogs()` | Clear network log | `clearNetworkLogs()` |
| `getResponseBody(id)` | Get response body | `getResponseBody(requestId)` |

### Screenshots

| Tool | Description | Example |
|------|-------------|---------|
| `screenshot()` | Full page screenshot | `screenshot()` |
| `screenshotElement(selector)` | Element screenshot | `screenshotElement('.slide')` |

---

## Troubleshooting

### Connection Issues

**Problem:** Cannot connect to Chrome

**Solutions:**
1. Verify Chrome is running with `--remote-debugging-port=9222`
2. Check port is not in use: `netstat -ano | findstr 9222`
3. Try different port: `--remote-debugging-port=9223`
4. Ensure firewall isn't blocking the port

**Problem:** MCP server won't start

**Solutions:**
1. Check Node.js version: `node --version` (need 18+)
2. Reinstall dependencies: `npm install`
3. Check for build errors: `npm run build`
4. Try global install: `npm install -g chrome-devtools-mcp`

### Debugging Tips

#### Enable Verbose Logging

```bash
DEBUG=chrome-devtools:* npm start
```

#### Check Chrome DevTools Protocol

Open `http://localhost:9222/json` in browser to see all debuggable targets

#### Test Connection

```javascript
// Simple connection test
try {
  await chrome_devtools.evaluate(() => 'OK');
  console.log('✓ Connection successful');
} catch (error) {
  console.error('✗ Connection failed:', error);
}
```

---

## Advanced Features

### Custom DevTools Protocol Commands

```javascript
// Access raw CDP (Chrome DevTools Protocol)
await chrome_devtools.send('Page.captureScreenshot', {
  format: 'png',
  clip: { x: 0, y: 0, width: 1920, height: 1080 }
});
```

### Multiple Browser Tabs

```javascript
// List all available targets
const targets = await fetch('http://localhost:9222/json').then(r => r.json());
console.log('Available tabs:', targets);

// Connect to specific tab
await chrome_devtools.connect(targets[1].webSocketDebuggerUrl);
```

### Automated Testing Workflow

```javascript
// Complete test scenario
async function testPresentationBuilder() {
  // 1. Navigate to page
  await chrome_devtools.navigate('http://localhost:8000/classroom-records/presentation/builder-v2');
  
  // 2. Check page loaded
  const title = await chrome_devtools.evaluate(() => document.title);
  console.assert(title.includes('Presentation Builder'), 'Wrong page');
  
  // 3. Add a slide
  await chrome_devtools.evaluate(() => {
    document.querySelector('button[onclick*="Add Slide"]').click();
  });
  
  // 4. Verify slide count increased
  const slideCount = await chrome_devtools.evaluate(() => {
    return document.querySelectorAll('.slide-thumb').length;
  });
  console.log('Total slides:', slideCount);
  
  // 5. Export presentation
  await chrome_devtools.evaluate(() => {
    document.querySelector('button[onclick*="Export JSON"]').click();
  });
  
  // 6. Check for errors
  const errors = await chrome_devtools.getConsoleMessages();
  const importErrors = errors.filter(e => e.text.includes('Invalid JSON'));
  
  if (importErrors.length > 0) {
    console.error('Import errors found:', importErrors);
  } else {
    console.log('✓ No import errors');
  }
}

// Run test
testPresentationBuilder();
```

### Performance Monitoring

```javascript
// Continuous performance monitoring
setInterval(async () => {
  const metrics = await chrome_devtools.evaluate(() => {
    return {
      memory: performance.memory ? performance.memory.usedJSHeapSize : null,
      fps: 0, // Would need rAF tracking
      domNodes: document.getElementsByTagName('*').length
    };
  });
  
  console.log('Performance metrics:', metrics);
}, 5000);
```

---

## Best Practices

### Security

1. **Never use remote debugging in production**
2. **Use custom user data directory** to avoid profile conflicts
3. **Close debugging Chrome** when done to free up port
4. **Don't expose debugging port** to network

### Performance

1. **Clean up listeners** when done
2. **Close browser** when debugging session ends
3. **Use selective queries** instead of querying entire DOM
4. **Batch evaluate calls** to reduce overhead

### Reliability

1. **Add error handling** to all evaluate calls
2. **Wait for elements** before interacting
3. **Check page state** before operations
4. **Use timeouts** for long-running operations

---

## Resources

- **GitHub Repository:** https://github.com/ChromeDevTools/chrome-devtools-mcp
- **Chrome DevTools Protocol:** https://chromedevtools.github.io/devtools-protocol/
- **MCP Specification:** https://modelcontextprotocol.io/
- **Puppeteer Docs:** https://pptr.dev/ (similar protocol)

---

## Summary

The Chrome DevTools MCP server is a powerful tool for automating browser interactions and debugging. For Laravel + Vue developers, it provides:

- ✅ Real-time Vue component inspection
- ✅ API call monitoring
- ✅ Performance analysis
- ✅ Automated testing capabilities
- ✅ AI-assisted debugging

Perfect for debugging complex issues like the Presentation Builder V2 JSON import fix!
