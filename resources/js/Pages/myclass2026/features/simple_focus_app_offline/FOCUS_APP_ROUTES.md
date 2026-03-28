# Simple Focus App Offline - Complete Routes Documentation

**Domain**: `https://qudratpro.com`

## 🚀 Quick Access Links

- **PWA Manifest**: https://qudratpro.com/simple-focus-app-offline/v2/manifest.webmanifest
- **App Icon**: https://qudratpro.com/simple-focus-app-offline/v2/icon.svg

### **Version 1 (Original)**
- **Main App**: https://qudratpro.com/simple-focus-app-offline/v1
- **With Cache Busting**: https://qudratpro.com/simple-focus-app-offline/v1?refresh=true
- **PWA Manifest**: https://qudratpro.com/simple-focus-app-offline/v1/manifest.webmanifest
- **App Icon**: https://qudratpro.com/simple-focus-app-offline/v1/icon.svg

---

## 📱 Features Overview

### **Core Features (Both Versions)**
- ✅ **No Authentication Required** - Open to everyone
- ✅ **Offline First** - Works without internet
- ✅ **PWA Installable** - Install as standalone app
- ✅ **DOS/Terminal UI** - Retro green-on-black interface
- ✅ **Local Storage** - Data saved in browser
- ✅ **Export/Import JSON** - Backup and restore data
- ✅ **10-Minute Timer** - Default focus sessions
- ✅ **Task Management** - Create and track tasks
- ✅ **Timeline Log** - Session history
- ✅ **Reset Everything** - Complete data wipe option

### **Enhanced Features (v2)**
- 🎯 **Focus Mood Mode** - Fullscreen distraction-free view
- 🔒 **Wake Lock** - Keep screen awake (first tab only)
- 🎨 **Improved UI** - Better colors and contrast
- 🎭 **Dynamic Timer Colors** - Green → Yellow → Red
- ⚠️ **Last Minute Warning** - Special alert
- 🔄 **Clear Cache Button** - Force fresh load
- 📱 **Floating Buttons** - Quick access controls

---

## 🛠️ Troubleshooting Cache Issues

### **If You See Old Version**
1. **Use Cache-Busting Link**: Add `?refresh=true` to the URL
2. **Clear Cache Button**: Use the "CLEAR CACHE" button in the app
3. **Hard Refresh**: `Ctrl+Shift+R` (Windows) or `Cmd+Shift+R` (Mac)
4. **Incognito Mode**: Open in private/incognito browser
5. **Telegram Browser**: Open link in Telegram app (fresh cache)

### **Cache-Busting Examples**
```
https://qudratpro.com/simple-focus-app-offline/v2?refresh=true
https://qudratpro.com/simple-focus-app-offline/v1?refresh=true
```

---

## 📋 Route Structure

### **Version 2 Routes**
| Route | URL | Purpose |
|-------|-----|---------|
| Main App | `/simple-focus-app-offline/v2` | Focus app v2 interface |
| Manifest | `/simple-focus-app-offline/v2/manifest.webmanifest` | PWA installation |
| Icon | `/simple-focus-app-offline/v2/icon.svg` | App icon with purple dots |

### **Version 1 Routes**
| Route | URL | Purpose |
|-------|-----|---------|
| Main App | `/simple-focus-app-offline/v1` | Focus app v1 interface |
| Manifest | `/simple-focus-app-offline/v1/manifest.webmanifest` | PWA installation |
| Icon | `/simple-focus-app-offline/v1/icon.svg` | Original app icon |

---

## 🎯 Wake Lock Feature

### **How It Works**
- **First Tab Only**: Only works in the first browser tab that opens
- **Screen Stay Awake**: Prevents screen timeout during focus sessions
- **Visual Indicators**: Cyan glowing button when active
- **Auto-Cleanup**: Releases when tab closes

### **Access Points**
- **Tools Panel**: "SCREEN ON/OFF" button
- **Floating Button**: Lock icon next to focus button

---

## 🎨 Visual Differences

### **Version 1 Icon**
- Green border and elements
- Simple checkmark design

### **Version 2 Icon**
- Brighter green (#4ade80) instead of dark green (#22c55e)
- Three purple dots indicating version 2
- Enhanced visual distinction

---

## 📱 PWA Installation

### **Install Steps**
1. Open the app URL
2. Look for "Install App" button in tools panel
3. Or use browser menu: "Add to Home Screen"
4. App installs as standalone application

### **Installation Requirements**
- **HTTPS Required** ✅ (Your domain has SSL)
- **Service Worker** ✅ (Configured)
- **Manifest File** ✅ (Available)
- **Valid Icons** ✅ (SVG icons provided)

---

## 🗂️ Data Storage

### **LocalStorage Keys**
- **v1**: `focus-app-state`
- **v2**: `focus-app-state-v2` (separate storage)
- **Wake Lock**: `focus-app-wake-lock-tab`
- **Version**: `focus-app-version`

### **Export/Import**
- **Format**: JSON
- **Location**: Downloaded/uploaded via tools panel
- **Compatibility**: v1 and v2 use same format

---

## 🚨 Emergency Reset

### **Reset Everything Button**
- **Location**: Tools panel (red pulsing button)
- **Function**: Complete data and cache wipe
- **Warning**: ⚠️ Cannot be undone
- **Result**: Fresh start with clean slate

### **Clear Cache Button**
- **Location**: Tools panel (orange button)
- **Function**: Clear browser cache and service worker
- **Result**: Forces fresh load of latest version

---

## 📞 Quick Reference

### **Recommended Links**
- **Production v2**: https://qudratpro.com/simple-focus-app-offline/v2
- **Development v2**: https://qudratpro.com/simple-focus-app-offline/v2?refresh=true
- **Legacy v1**: https://qudratpro.com/simple-focus-app-offline/v1

### **Problem Solving**
1. **Old Version?** → Use `?refresh=true`
2. **Cache Issues?** → Click "CLEAR CACHE"
3. **Screen Turns Off?** → Use wake lock (first tab only)
4. **Want Focus Mode?** → Click "FOCUS MOOD" or floating button

---

## 📅 Version History

### **v2.0** (Current)
- Enhanced UI with better colors
- Focus Mood fullscreen mode
- Wake Lock screen stay awake
- Floating control buttons
- Dynamic timer colors
- Last minute warnings
- Clear cache functionality

### **v1.0** (Legacy)
- Basic DOS-style interface
- 10-minute timer
- Task management
- Timeline logging
- Export/Import JSON
- PWA installation

---

**Last Updated**: March 27, 2026  
**Domain**: https://qudratpro.com  
**Versions**: v1 (Legacy) | v2 (Current)
