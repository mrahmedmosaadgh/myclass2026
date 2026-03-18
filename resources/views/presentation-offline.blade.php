<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Offline - Presentation Builder</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Figtree', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .offline-container {
            background: white;
            border-radius: 16px;
            padding: 60px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }

        .offline-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        h1 {
            color: #1a202c;
            font-size: 32px;
            margin-bottom: 16px;
            font-weight: 700;
        }

        p {
            color: #4a5568;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #fed7d7;
            color: #c53030;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 30px;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            background: #c53030;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.5;
                transform: scale(1.2);
            }
        }

        .retry-btn {
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .retry-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .retry-btn:active {
            transform: translateY(0);
        }

        .tips {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e2e8f0;
        }

        .tips h3 {
            color: #2d3748;
            font-size: 18px;
            margin-bottom: 16px;
        }

        .feature-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .feature-item {
            padding: 16px;
            background: #f7fafc;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            text-align: left;
        }

        .feature-item strong {
            color: #2d3748;
            display: block;
            margin-bottom: 4px;
            font-size: 15px;
        }

        .feature-item span {
            color: #718096;
            font-size: 14px;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-icon">📡</div>
        
        <h1>You're Offline</h1>
        
        <div class="status-indicator">
            <div class="status-dot"></div>
            <span>No Internet Connection</span>
        </div>

        <p>
            Don't worry! You can still access your cached presentations and continue working.
            Your changes will be saved locally and synced when you're back online.
        </p>

        <button class="retry-btn" onclick="window.location.reload()">
            🔄 Try Again
        </button>

        <a href="/" class="back-link">← Back to Home</a>

        <div class="tips">
            <h3>💡 Available Offline Features:</h3>
            <div class="feature-list">
                <div class="feature-item">
                    <strong>📊 View Presentations</strong>
                    <span>Access cached slides</span>
                </div>
                <div class="feature-item">
                    <strong>✏️ Edit Slides</strong>
                    <span>Work on presentations</span>
                </div>
                <div class="feature-item">
                    <strong>🎨 Add Elements</strong>
                    <span>Images, text, and more</span>
                </div>
                <div class="feature-item">
                    <strong>💾 Local Saving</strong>
                    <span>Auto-sync when online</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Listen for online status
        window.addEventListener('online', () => {
            if (navigator.onLine) {
                setTimeout(() => {
                    if (confirm('You\'re back online! Reload the page?')) {
                        window.location.reload();
                    }
                }, 1000);
            }
        });

        // Check initial status
        if (!navigator.onLine) {
            console.log('Currently offline - showing offline page');
        }
    </script>
</body>
</html>
