# Offline Schedule Table App

A Progressive Web App (PWA) that displays a weekly class schedule with live indicators and offline support.

## 🚀 Quick Start

### Method 1: Use the Start Script
```bash
cd /path/to/table/directory
./start-server.sh
```

### Method 2: Manual Start
```bash
# Using Python 3 (recommended)
python3 -m http.server 8000

# Using Python 2
python -m SimpleHTTPServer 8000

# Using Node.js
npx http-server -p 8000
```

Then open: **http://localhost:8000**

## ✨ Features

### 📅 Schedule Display
- Weekly class timetable
- Color-coded subjects (7A, 4A, others)
- NAFS indicator for special classes
- Current period highlighting
- Live countdown timer

### 📱 PWA Features
- **Offline Support**: Works without internet connection
- **Installable**: Can be installed as a mobile app
- **Notifications**: Alerts when classes start
- **Responsive**: Works on all screen sizes

### 🎯 Interactive Features
- **Live Indicators**: Shows current class with red glow
- **Countdown Timer**: Time remaining in current period
- **Today Filter**: Show only today's schedule
- **Audio Alerts**: Plays sound when classes start

## 🔧 Technical Details

### Files Structure
```
table/
├── index.html          # Main app
├── full_schedule.json  # Schedule data
├── sw.js              # Service worker (offline support)
├── manifest.json      # PWA manifest
├── icon.png           # App icon
├── notification1.mp3  # Alert sound
├── offline.html       # Offline fallback page
└── start-server.sh    # Easy start script
```

### Service Worker
- Caches all essential files
- Serves cached content when offline
- Updates cache when new version available
- Console logging for debugging

### Data Format
```json
[
  {
    "day": "Sunday",
    "dayIndex": 0,
    "classes": [
      { "p": 1, "sub": "7A" },
      { "p": 2, "sub": "", "nafs": true }
    ]
  }
]
```

## 🐛 Troubleshooting

### Common Issues

1. **"Could not load full_schedule.json"**
   - ✅ Make sure you're using a web server (not file://)
   - ✅ Check that full_schedule.json exists
   - ✅ Try refreshing the page

2. **"Service Worker registration failed"**
   - ✅ Use HTTPS or localhost
   - ✅ Check browser console for errors
   - ✅ Clear browser cache and retry

3. **Audio not playing**
   - ✅ Interact with the page first (click anywhere)
   - ✅ Check browser audio permissions
   - ✅ Ensure notification1.mp3 exists

4. **Offline mode not working**
   - ✅ Check service worker is registered (console)
   - ✅ Try refreshing while online first
   - ✅ Clear cache and reload

### Debug Mode
Open browser console (F12) to see:
- Service worker registration logs
- Cache status
- Network requests
- Error messages

## 📱 Mobile Installation

1. Open the app in mobile browser
2. Look for "Add to Home Screen" prompt
3. Or use browser menu → "Add to Home Screen"
4. App will work offline after installation

## 🔄 Updates

When you update the app:
1. Service worker will detect changes
2. New version will be cached
3. Refresh to get latest version

## 🎨 Customization

### Change Colors
Edit CSS variables in `index.html`:
```css
:root {
    --7a-color: #ffff00;  // 7A classes
    --4a-color: #dbeafe;  // 4A classes
    --other-color: #ffffff; // Other subjects
}
```

### Add Subjects
Update `full_schedule.json` with your schedule data.

### Modify Time Slots
Update the `timeSlots` array in JavaScript.

## 🌐 Network Requirements

- **First Load**: Requires internet connection
- **Offline Use**: Works fully offline after first load
- **Updates**: Needs connection to get latest schedule

## 📊 Browser Support

- ✅ Chrome (full support)
- ✅ Firefox (full support)
- ✅ Safari (full support)
- ✅ Edge (full support)
- ⚠️ IE (not supported)

## 🔒 Security

- Uses HTTPS for production
- Safe service worker implementation
- No external dependencies
- Local data only
