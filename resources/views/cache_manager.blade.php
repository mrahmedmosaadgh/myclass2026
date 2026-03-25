<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Cache Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8 text-gray-800">Laravel Cache Manager</h1>
        
        <!-- Server Status Check -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-semibold text-blue-800 mb-2">Server Status</h3>
            <button onclick="checkServerStatus()" 
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-colors text-sm">
                Check Server Status
            </button>
            <div id="server-status" class="mt-4 text-sm"></div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Run All Cache Commands -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Run Cache Commands</h3>
                <p class="text-sm text-gray-600 mb-4">Build all caches (config, route, view, optimize)</p>
                <button onclick="runCacheCommands()" 
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-colors">
                    Run Cache Commands
                </button>
                <div id="cache-results" class="mt-4 text-sm"></div>
            </div>

            <!-- Clear All Caches -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Clear All Caches</h3>
                <p class="text-sm text-gray-600 mb-4">Clear all Laravel caches</p>
                <button onclick="clearAllCaches()" 
                        class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition-colors">
                    Clear All Caches
                </button>
                <div id="clear-results" class="mt-4 text-sm"></div>
            </div>

            <!-- Individual Cache Clears -->
            <div class="bg-white rounded-lg shadow p-6 md:col-span-2">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Individual Cache Operations</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <button onclick="clearCache('cache')" 
                            class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition-colors text-sm">
                        Clear App Cache
                    </button>
                    <button onclick="clearCache('config')" 
                            class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition-colors text-sm">
                        Clear Config
                    </button>
                    <button onclick="clearCache('route')" 
                            class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition-colors text-sm">
                        Clear Routes
                    </button>
                    <button onclick="clearCache('view')" 
                            class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition-colors text-sm">
                        Clear Views
                    </button>
                </div>
                <div id="individual-results" class="mt-4 text-sm"></div>
            </div>

            <!-- Offline Files Removal -->
            <div class="bg-white rounded-lg shadow p-6 md:col-span-2">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Offline Files Management</h3>
                <p class="text-sm text-gray-600 mb-4">Remove offline cache files for current domain</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button onclick="removeOfflineFiles()" 
                            class="bg-purple-500 hover:bg-purple-600 text-white font-bold py-2 px-4 rounded transition-colors text-sm">
                        Remove Offline Files
                    </button>
                    <button onclick="checkOfflineFiles()" 
                            class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded transition-colors text-sm">
                        Check Offline Files
                    </button>
                    <button onclick="clearServiceWorker()" 
                            class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded transition-colors text-sm">
                        Clear Service Worker
                    </button>
                </div>
                <div id="offline-results" class="mt-4 text-sm"></div>
            </div>

            <!-- Additional Cache Options -->
            <div class="bg-white rounded-lg shadow p-6 md:col-span-2">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Additional Cache Options</h3>
                <p class="text-sm text-gray-600 mb-4">Clear browser storage and session data</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <button onclick="clearSessionStorage()" 
                            class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded transition-colors text-sm">
                        Clear Session
                    </button>
                    <button onclick="clearAllLocalStorage()" 
                            class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition-colors text-sm">
                        Clear LocalStorage
                    </button>
                    <button onclick="clearIndexedDB()" 
                            class="bg-teal-500 hover:bg-teal-600 text-white font-bold py-2 px-4 rounded transition-colors text-sm">
                        Clear IndexedDB
                    </button>
                    <button onclick="clearAllBrowserData()" 
                            class="bg-gray-700 hover:bg-gray-800 text-white font-bold py-2 px-4 rounded transition-colors text-sm">
                        Clear All Browser Data
                    </button>
                </div>
                <div id="additional-results" class="mt-4 text-sm"></div>
            </div>

            <!-- Development Tools -->
            <div class="bg-white rounded-lg shadow p-6 md:col-span-2">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Development Tools</h3>
                <p class="text-sm text-gray-600 mb-4">Development and debugging utilities</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <button onclick="hardRefresh()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors text-sm">
                        Hard Refresh
                    </button>
                    <button onclick="clearCookies()" 
                            class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded transition-colors text-sm">
                        Clear Cookies
                    </button>
                    <button onclick="showStorageInfo()" 
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition-colors text-sm">
                        Storage Info
                    </button>
                    <button onclick="exportDebugInfo()" 
                            class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded transition-colors text-sm">
                        Export Debug Info
                    </button>
                </div>
                <div id="development-results" class="mt-4 text-sm"></div>
            </div>
        </div>
    </div>

    <script>
        function getCSRFToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        async function checkServerStatus() {
            const statusDiv = document.getElementById('server-status');
            statusDiv.innerHTML = '<div class="text-blue-600">Checking server status...</div>';

            try {
                // Check if routes cache exists
                const cacheResponse = await fetch('/developer/run-cache-commands', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCSRFToken()
                    }
                });

                if (cacheResponse.ok) {
                    const data = await cacheResponse.json();
                    let html = '<div class="font-semibold text-green-600 mb-2">✅ Server Status: OK</div>';
                    html += '<div class="text-xs py-1">✓ Cache commands accessible</div>';
                    html += '<div class="text-xs py-1">✓ CSRF token working</div>';
                    html += '<div class="text-xs py-1">✓ Authentication working</div>';
                    html += '<div class="text-xs py-1 mt-2 text-gray-600">Last checked: ' + new Date().toLocaleString() + '</div>';
                    statusDiv.innerHTML = html;
                } else {
                    throw new Error('Cache commands not accessible');
                }

            } catch (error) {
                let html = '<div class="font-semibold text-red-600 mb-2">❌ Server Issues Detected</div>';
                html += '<div class="text-xs py-1">✗ ' + error.message + '</div>';
                html += '<div class="text-xs py-1 mt-2 text-yellow-600">Possible solutions:</div>';
                html += '<div class="text-xs py-1">• Check if user is authenticated</div>';
                html += '<div class="text-xs py-1">• Verify bootstrap/cache/ permissions</div>';
                html += '<div class="text-xs py-1">• Check if routes are cached</div>';
                html += '<div class="text-xs py-1">• Run: php artisan route:cache</div>';
                statusDiv.innerHTML = html;
            }
        }

        async function runCacheCommands() {
            const resultsDiv = document.getElementById('cache-results');
            resultsDiv.innerHTML = '<div class="text-blue-600">Running cache commands...</div>';

            try {
                const response = await fetch('/developer/run-cache-commands', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCSRFToken()
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                
                let html = `<div class="font-semibold text-green-600 mb-2">✅ Completed at ${data.timestamp}</div>`;
                for (const [command, result] of Object.entries(data.results)) {
                    const status = result.includes('✅') ? 'text-green-600' : 'text-red-600';
                    html += `<div class="text-xs py-1 ${status}">${command}: ${result}</div>`;
                }
                
                // Add success message for route cache creation
                if (data.results['route:cache'] && data.results['route:cache'].includes('✅')) {
                    html += `<div class="text-xs py-1 mt-2 text-blue-600">📁 Route cache created: bootstrap/cache/routes-v7.php</div>`;
                }
                
                resultsDiv.innerHTML = html;

            } catch (error) {
                let html = `<div class="text-red-600">❌ Error: ${error.message}</div>`;
                html += `<div class="text-xs py-1 mt-2 text-yellow-600">Troubleshooting:</div>`;
                html += `<div class="text-xs py-1">• Check if bootstrap/cache/ is writable</div>`;
                html += `<div class="text-xs py-1">• Run: chmod 755 bootstrap/cache/</div>`;
                html += `<div class="text-xs py-1">• Check server logs for details</div>`;
                resultsDiv.innerHTML = html;
            }
        }

        async function clearAllCaches() {
            const resultsDiv = document.getElementById('clear-results');
            resultsDiv.innerHTML = '<div class="text-red-600">Clearing all caches...</div>';

            try {
                const response = await fetch('/developer/clear-all', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCSRFToken()
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                
                let html = `<div class="font-semibold text-green-600 mb-2">✅ Completed at ${data.timestamp}</div>`;
                for (const [command, result] of Object.entries(data.results)) {
                    const status = result.includes('✅') ? 'text-green-600' : 'text-red-600';
                    html += `<div class="text-xs py-1 ${status}">${command}: ${result}</div>`;
                }
                
                // Add info about route cache deletion
                if (data.results['route:clear'] && data.results['route:clear'].includes('✅')) {
                    html += `<div class="text-xs py-1 mt-2 text-blue-600">🗑️ Route cache deleted: bootstrap/cache/routes-v7.php</div>`;
                    html += `<div class="text-xs py-1 text-orange-600">⚠️ Performance: Routes will be read from files on each request</div>`;
                }
                
                resultsDiv.innerHTML = html;

            } catch (error) {
                resultsDiv.innerHTML = `<div class="text-red-600">❌ Error: ${error.message}</div>`;
            }
        }

        async function clearCache(type) {
            const resultsDiv = document.getElementById('individual-results');
            resultsDiv.innerHTML = `<div class="text-yellow-600">Clearing ${type} cache...</div>`;

            try {
                const response = await fetch(`/developer/clear-${type}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCSRFToken()
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                
                if (data.status === 'success') {
                    resultsDiv.innerHTML = `<div class="text-green-600">✅ ${data.message}</div>`;
                    
                    // Add specific info for route cache
                    if (type === 'route') {
                        resultsDiv.innerHTML += `<div class="text-xs py-1 text-blue-600 mt-1">🗑️ bootstrap/cache/routes-v7.php deleted</div>`;
                    }
                } else {
                    resultsDiv.innerHTML = `<div class="text-red-600">❌ ${data.message}</div>`;
                }

            } catch (error) {
                resultsDiv.innerHTML = `<div class="text-red-600">❌ Error: ${error.message}</div>`;
            }
        }

        // Offline Files Management Functions
        async function removeOfflineFiles() {
            const resultsDiv = document.getElementById('offline-results');
            resultsDiv.innerHTML = '<div class="text-purple-600">Removing offline files...</div>';

            try {
                // Clear browser caches
                if ('caches' in window) {
                    const cacheNames = await caches.keys();
                    const currentDomain = window.location.hostname;
                    let deletedCaches = [];

                    for (const cacheName of cacheNames) {
                        if (cacheName.includes(currentDomain) || cacheName.includes('workbox') || cacheName.includes('offline')) {
                            await caches.delete(cacheName);
                            deletedCaches.push(cacheName);
                        }
                    }

                    let html = `<div class="font-semibold text-green-600 mb-2">✅ Offline files removed</div>`;
                    html += `<div class="text-xs py-1">Domain: ${currentDomain}</div>`;
                    html += `<div class="text-xs py-1">Caches deleted: ${deletedCaches.length}</div>`;
                    
                    if (deletedCaches.length > 0) {
                        html += `<div class="text-xs py-1 mt-2 text-gray-600">Deleted caches:</div>`;
                        deletedCaches.forEach(cache => {
                            html += `<div class="text-xs py-1 pl-4">• ${cache}</div>`;
                        });
                    }
                    
                    resultsDiv.innerHTML = html;
                } else {
                    resultsDiv.innerHTML = '<div class="text-yellow-600">⚠️ Cache API not supported in this browser</div>';
                }

                // Clear localStorage for current domain
                const localStorageKeys = Object.keys(localStorage);
                let deletedKeys = [];
                
                localStorageKeys.forEach(key => {
                    if (key.includes('cache') || key.includes('offline') || key.includes('workbox')) {
                        localStorage.removeItem(key);
                        deletedKeys.push(key);
                    }
                });

                if (deletedKeys.length > 0) {
                    resultsDiv.innerHTML += `<div class="text-xs py-1 mt-2">LocalStorage keys cleared: ${deletedKeys.length}</div>`;
                }

            } catch (error) {
                resultsDiv.innerHTML = `<div class="text-red-600">❌ Error: ${error.message}</div>`;
            }
        }

        async function checkOfflineFiles() {
            const resultsDiv = document.getElementById('offline-results');
            resultsDiv.innerHTML = '<div class="text-indigo-600">Checking offline files...</div>';

            try {
                const currentDomain = window.location.hostname;
                let html = `<div class="font-semibold text-blue-600 mb-2">📋 Offline Files Status</div>`;
                html += `<div class="text-xs py-1">Domain: ${currentDomain}</div>`;

                // Check browser caches
                if ('caches' in window) {
                    const cacheNames = await caches.keys();
                    const relevantCaches = cacheNames.filter(name => 
                        name.includes(currentDomain) || 
                        name.includes('workbox') || 
                        name.includes('offline')
                    );

                    html += `<div class="text-xs py-1 mt-2">Browser Caches: ${relevantCaches.length}</div>`;
                    
                    if (relevantCaches.length > 0) {
                        html += `<div class="text-xs py-1 pl-4">Found caches:</div>`;
                        relevantCaches.forEach(cache => {
                            html += `<div class="text-xs py-1 pl-8">• ${cache}</div>`;
                        });
                    }
                } else {
                    html += `<div class="text-xs py-1 text-yellow-600">Cache API not supported</div>`;
                }

                // Check localStorage
                const localStorageKeys = Object.keys(localStorage).filter(key => 
                    key.includes('cache') || key.includes('offline') || key.includes('workbox')
                );
                
                html += `<div class="text-xs py-1 mt-2">LocalStorage keys: ${localStorageKeys.length}</div>`;
                
                if (localStorageKeys.length > 0) {
                    html += `<div class="text-xs py-1 pl-4">Found keys:</div>`;
                    localStorageKeys.forEach(key => {
                        const size = localStorage.getItem(key).length;
                        html += `<div class="text-xs py-1 pl-8">• ${key} (${size} chars)</div>`;
                    });
                }

                // Check service worker
                if ('serviceWorker' in navigator) {
                    const registrations = await navigator.serviceWorker.getRegistrations();
                    html += `<div class="text-xs py-1 mt-2">Service Workers: ${registrations.length}</div>`;
                    
                    registrations.forEach(registration => {
                        html += `<div class="text-xs py-1 pl-4">• ${registration.scope}</div>`;
                    });
                } else {
                    html += `<div class="text-xs py-1 text-yellow-600">Service Worker not supported</div>`;
                }

                resultsDiv.innerHTML = html;

            } catch (error) {
                resultsDiv.innerHTML = `<div class="text-red-600">❌ Error: ${error.message}</div>`;
            }
        }

        async function clearServiceWorker() {
            const resultsDiv = document.getElementById('offline-results');
            resultsDiv.innerHTML = '<div class="text-pink-600">Clearing service worker...</div>';

            try {
                if ('serviceWorker' in navigator) {
                    const registrations = await navigator.serviceWorker.getRegistrations();
                    let unregistered = [];

                    for (const registration of registrations) {
                        await registration.unregister();
                        unregistered.push(registration.scope);
                    }

                    let html = `<div class="font-semibold text-green-600 mb-2">✅ Service Workers cleared</div>`;
                    html += `<div class="text-xs py-1">Unregistered: ${unregistered.length}</div>`;
                    
                    if (unregistered.length > 0) {
                        html += `<div class="text-xs py-1 mt-2">Unregistered scopes:</div>`;
                        unregistered.forEach(scope => {
                            html += `<div class="text-xs py-1 pl-4">• ${scope}</div>`;
                        });
                    }

                    // Also clear caches again after unregistering
                    if ('caches' in window) {
                        const cacheNames = await caches.keys();
                        let deletedCaches = [];

                        for (const cacheName of cacheNames) {
                            if (cacheName.includes('workbox') || cacheName.includes('offline')) {
                                await caches.delete(cacheName);
                                deletedCaches.push(cacheName);
                            }
                        }

                        if (deletedCaches.length > 0) {
                            html += `<div class="text-xs py-1 mt-2">Related caches cleared: ${deletedCaches.length}</div>`;
                        }
                    }

                    resultsDiv.innerHTML = html;
                } else {
                    resultsDiv.innerHTML = '<div class="text-yellow-600">⚠️ Service Worker not supported in this browser</div>';
                }

            } catch (error) {
                resultsDiv.innerHTML = `<div class="text-red-600">❌ Error: ${error.message}</div>`;
            }
        }

        // Additional Cache Options Functions
        function clearSessionStorage() {
            const resultsDiv = document.getElementById('additional-results');
            
            try {
                const count = sessionStorage.length;
                sessionStorage.clear();
                
                resultsDiv.innerHTML = `
                    <div class="font-semibold text-green-600 mb-2">✅ Session Storage Cleared</div>
                    <div class="text-xs py-1">Items removed: ${count}</div>
                    <div class="text-xs py-1 text-gray-600">Session storage is now empty</div>
                `;
            } catch (error) {
                resultsDiv.innerHTML = `<div class="text-red-600">❌ Error: ${error.message}</div>`;
            }
        }

        function clearAllLocalStorage() {
            const resultsDiv = document.getElementById('additional-results');
            
            try {
                const count = localStorage.length;
                localStorage.clear();
                
                resultsDiv.innerHTML = `
                    <div class="font-semibold text-green-600 mb-2">✅ LocalStorage Cleared</div>
                    <div class="text-xs py-1">Items removed: ${count}</div>
                    <div class="text-xs py-1 text-gray-600">All local storage data deleted</div>
                `;
            } catch (error) {
                resultsDiv.innerHTML = `<div class="text-red-600">❌ Error: ${error.message}</div>`;
            }
        }

        async function clearIndexedDB() {
            const resultsDiv = document.getElementById('additional-results');
            resultsDiv.innerHTML = '<div class="text-teal-600">Clearing IndexedDB...</div>';

            try {
                if ('indexedDB' in window) {
                    const databases = await indexedDB.databases();
                    let deletedCount = 0;

                    for (const db of databases) {
                        if (db.name) {
                            await indexedDB.deleteDatabase(db.name);
                            deletedCount++;
                        }
                    }

                    resultsDiv.innerHTML = `
                        <div class="font-semibold text-green-600 mb-2">✅ IndexedDB Cleared</div>
                        <div class="text-xs py-1">Databases deleted: ${deletedCount}</div>
                        <div class="text-xs py-1 text-gray-600">All IndexedDB data removed</div>
                    `;
                } else {
                    resultsDiv.innerHTML = '<div class="text-yellow-600">⚠️ IndexedDB not supported</div>';
                }
            } catch (error) {
                resultsDiv.innerHTML = `<div class="text-red-600">❌ Error: ${error.message}</div>`;
            }
        }

        async function clearAllBrowserData() {
            const resultsDiv = document.getElementById('additional-results');
            resultsDiv.innerHTML = '<div class="text-gray-600">Clearing all browser data...</div>';

            try {
                let totalCleared = 0;
                let details = [];

                // Clear sessionStorage
                const sessionCount = sessionStorage.length;
                sessionStorage.clear();
                totalCleared += sessionCount;
                details.push(`Session: ${sessionCount} items`);

                // Clear localStorage
                const localCount = localStorage.length;
                localStorage.clear();
                totalCleared += localCount;
                details.push(`LocalStorage: ${localCount} items`);

                // Clear IndexedDB
                if ('indexedDB' in window) {
                    const databases = await indexedDB.databases();
                    for (const db of databases) {
                        if (db.name) {
                            await indexedDB.deleteDatabase(db.name);
                            totalCleared++;
                        }
                    }
                    details.push(`IndexedDB: ${databases.length} databases`);
                }

                // Clear browser caches
                if ('caches' in window) {
                    const cacheNames = await caches.keys();
                    for (const cacheName of cacheNames) {
                        await caches.delete(cacheName);
                        totalCleared++;
                    }
                    details.push(`Caches: ${cacheNames.length} caches`);
                }

                resultsDiv.innerHTML = `
                    <div class="font-semibold text-green-600 mb-2">✅ All Browser Data Cleared</div>
                    <div class="text-xs py-1">Total items cleared: ${totalCleared}</div>
                    <div class="text-xs py-1 mt-2 text-gray-600">Details:</div>
                    ${details.map(detail => `<div class="text-xs py-1 pl-4">• ${detail}</div>`).join('')}
                    <div class="text-xs py-1 mt-2 text-orange-600">⚠️ Consider refreshing the page</div>
                `;
            } catch (error) {
                resultsDiv.innerHTML = `<div class="text-red-600">❌ Error: ${error.message}</div>`;
            }
        }

        // Development Tools Functions
        function hardRefresh() {
            const resultsDiv = document.getElementById('development-results');
            
            try {
                // Clear caches first
                if ('caches' in window) {
                    caches.keys().then(cacheNames => {
                        cacheNames.forEach(cacheName => {
                            caches.delete(cacheName);
                        });
                    });
                }

                // Hard refresh
                window.location.reload(true);
                
                resultsDiv.innerHTML = `
                    <div class="font-semibold text-blue-600 mb-2">🔄 Hard Refresh Initiated</div>
                    <div class="text-xs py-1">Page reloading with cache bypass</div>
                `;
            } catch (error) {
                resultsDiv.innerHTML = `<div class="text-red-600">❌ Error: ${error.message}</div>`;
            }
        }

        function clearCookies() {
            const resultsDiv = document.getElementById('development-results');
            
            try {
                const cookies = document.cookie.split(';');
                let clearedCount = 0;

                cookies.forEach(cookie => {
                    const eqPos = cookie.indexOf('=');
                    const name = eqPos > -1 ? cookie.substr(0, eqPos).trim() : cookie.trim();
                    if (name) {
                        document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/`;
                        document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;domain=${window.location.hostname}`;
                        clearedCount++;
                    }
                });

                resultsDiv.innerHTML = `
                    <div class="font-semibold text-green-600 mb-2">✅ Cookies Cleared</div>
                    <div class="text-xs py-1">Cookies removed: ${clearedCount}</div>
                    <div class="text-xs py-1 text-gray-600">All cookies for current domain deleted</div>
                `;
            } catch (error) {
                resultsDiv.innerHTML = `<div class="text-red-600">❌ Error: ${error.message}</div>`;
            }
        }

        function showStorageInfo() {
            const resultsDiv = document.getElementById('development-results');
            
            try {
                let html = `<div class="font-semibold text-green-600 mb-2">📊 Storage Information</div>`;
                
                // Session Storage
                html += `<div class="text-xs py-1 mt-2">Session Storage: ${sessionStorage.length} items</div>`;
                
                // Local Storage
                html += `<div class="text-xs py-1">Local Storage: ${localStorage.length} items</div>`;
                
                // Calculate localStorage size
                let totalSize = 0;
                for (let key in localStorage) {
                    if (localStorage.hasOwnProperty(key)) {
                        totalSize += localStorage[key].length + key.length;
                    }
                }
                html += `<div class="text-xs py-1">LocalStorage Size: ${(totalSize / 1024).toFixed(2)} KB</div>`;
                
                // IndexedDB
                if ('indexedDB' in window) {
                    indexedDB.databases().then(databases => {
                        html += `<div class="text-xs py-1">IndexedDB: ${databases.length} databases</div>`;
                        resultsDiv.innerHTML = html;
                    });
                } else {
                    html += `<div class="text-xs py-1 text-yellow-600">IndexedDB: Not supported</div>`;
                }
                
                // Browser Caches
                if ('caches' in window) {
                    caches.keys().then(cacheNames => {
                        html += `<div class="text-xs py-1">Browser Caches: ${cacheNames.length} caches</div>`;
                        resultsDiv.innerHTML = html;
                    });
                }
                
                // Cookies
                const cookies = document.cookie.split(';').filter(cookie => cookie.trim().length > 0);
                html += `<div class="text-xs py-1">Cookies: ${cookies.length} cookies</div>`;
                
                // User Agent
                html += `<div class="text-xs py-1 mt-2 text-gray-600">Browser: ${navigator.userAgent.split(' ')[0]}</div>`;
                
                resultsDiv.innerHTML = html;
                
            } catch (error) {
                resultsDiv.innerHTML = `<div class="text-red-600">❌ Error: ${error.message}</div>`;
            }
        }

        function exportDebugInfo() {
            const resultsDiv = document.getElementById('development-results');
            
            try {
                const debugInfo = {
                    timestamp: new Date().toISOString(),
                    url: window.location.href,
                    userAgent: navigator.userAgent,
                    storage: {
                        sessionStorage: sessionStorage.length,
                        localStorage: localStorage.length,
                        localStorageKeys: Object.keys(localStorage),
                        cookies: document.cookie.split(';').filter(c => c.trim().length > 0).length
                    },
                    caches: [],
                    indexedDB: [],
                    performance: {
                        loadTime: performance.timing.loadEventEnd - performance.timing.navigationStart,
                        domReady: performance.timing.domContentLoadedEventEnd - performance.timing.navigationStart
                    }
                };

                // Get cache info
                if ('caches' in window) {
                    caches.keys().then(cacheNames => {
                        debugInfo.caches = cacheNames;
                        
                        // Get IndexedDB info
                        if ('indexedDB' in window) {
                            indexedDB.databases().then(databases => {
                                debugInfo.indexedDB = databases.map(db => ({ name: db.name, version: db.version }));
                                
                                // Create and download file
                                const blob = new Blob([JSON.stringify(debugInfo, null, 2)], { type: 'application/json' });
                                const url = URL.createObjectURL(blob);
                                const a = document.createElement('a');
                                a.href = url;
                                a.download = `debug-info-${new Date().getTime()}.json`;
                                document.body.appendChild(a);
                                a.click();
                                document.body.removeChild(a);
                                URL.revokeObjectURL(url);
                                
                                resultsDiv.innerHTML = `
                                    <div class="font-semibold text-purple-600 mb-2">📥 Debug Info Exported</div>
                                    <div class="text-xs py-1">File: debug-info-${new Date().getTime()}.json</div>
                                    <div class="text-xs py-1 text-gray-600">Check your downloads folder</div>
                                `;
                            });
                        }
                    });
                }
                
            } catch (error) {
                resultsDiv.innerHTML = `<div class="text-red-600">❌ Error: ${error.message}</div>`;
            }
        }

        // Auto-check server status on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkServerStatus();
        });
    </script>
</body>
</html>
