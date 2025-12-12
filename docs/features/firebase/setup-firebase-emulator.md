# Firebase Emulator Setup Instructions

## ✅ Configuration Files Created

I've created the necessary Firebase configuration files:
- `firebase.json` - Emulator configuration
- `.firebaserc` - Firebase project configuration

## 📦 Step 1: Install Firebase CLI

Choose one of the following methods:

### Option A: Using npm (Recommended)
```bash
npm install -g firebase-tools
```

### Option B: Using Standalone Binary (Windows)
Download from: https://firebase.tools/bin/win/instant/latest

### Option C: Using Chocolatey (Windows)
```bash
choco install firebase-cli
```

## 🔐 Step 2: Login to Firebase

```bash
firebase login
```

This will open your browser for authentication.

## ✅ Step 3: Verify Installation

```bash
firebase --version
```

You should see the Firebase CLI version number.

## 🚀 Step 4: Start the Emulators

```bash
firebase emulators:start
```

Or start specific emulators:
```bash
firebase emulators:start --only auth,database
```

## 🌐 Step 5: Access Emulator UI

Once started, open your browser to:
```
http://localhost:4000
```

You'll see the Firebase Emulator Suite UI with:
- Authentication Emulator (port 9099)
- Realtime Database Emulator (port 9000)

## 🧪 Step 6: Test Your Application

1. **Start Laravel:**
```bash
php artisan serve
```

2. **Start Vite (in another terminal):**
```bash
npm run dev
```

3. **Open your application:**
```
http://localhost:8000/quiz/live/teacher
```

4. **Check browser console for:**
```
🔧 Development mode detected - connecting to Firebase emulators
✅ Connected to Auth Emulator at http://localhost:9099
✅ Signed in anonymously to Firebase
```

## 📊 Emulator Ports

| Service | Port | URL |
|---------|------|-----|
| Emulator UI | 4000 | http://localhost:4000 |
| Auth Emulator | 9099 | http://localhost:9099 |
| Database Emulator | 9000 | http://localhost:9000 |

## 🔧 Troubleshooting

### Issue: "firebase: command not found"
**Solution:** Firebase CLI not installed. Follow Step 1 above.

### Issue: "Port already in use"
**Solution:** Stop other services using these ports or change ports in `firebase.json`

### Issue: "ECONNREFUSED localhost:9099"
**Solution:** Emulators not running. Run `firebase emulators:start`

### Issue: Still seeing production Firebase requests
**Solution:** 
1. Verify you're accessing via `http://localhost:8000` (not 127.0.0.1)
2. Check browser console for emulator connection messages
3. Clear browser cache and reload

## 📝 Development Workflow

### Recommended Terminal Setup:

**Terminal 1 - Firebase Emulators:**
```bash
firebase emulators:start
```

**Terminal 2 - Laravel:**
```bash
php artisan serve
```

**Terminal 3 - Vite:**
```bash
npm run dev
```

## 🎯 Quick Start Script

Create a file `start-dev.bat` (Windows):
```batch
@echo off
echo Starting Firebase Emulators...
start cmd /k "firebase emulators:start"

timeout /t 5

echo Starting Laravel...
start cmd /k "php artisan serve"

echo Starting Vite...
start cmd /k "npm run dev"

echo All services started!
echo.
echo Firebase Emulator UI: http://localhost:4000
echo Laravel App: http://localhost:8000
echo.
pause
```

Then just run:
```bash
start-dev.bat
```

## ✅ Verification Checklist

After setup, verify:
- [ ] Firebase CLI installed (`firebase --version`)
- [ ] Logged into Firebase (`firebase login`)
- [ ] Emulators start successfully (`firebase emulators:start`)
- [ ] Emulator UI accessible at http://localhost:4000
- [ ] Application connects to emulator (check browser console)
- [ ] No 400 errors to identitytoolkit.googleapis.com
- [ ] Auth requests go to localhost:9099

## 🎉 Success!

Once you see these messages in your browser console:
```
🔧 Development mode detected - connecting to Firebase emulators
✅ Connected to Auth Emulator at http://localhost:9099
✅ Signed in anonymously to Firebase
```

Your Firebase Emulator is working correctly!

## 📚 Additional Resources

- [Firebase Emulator Suite Docs](https://firebase.google.com/docs/emulator-suite)
- [Firebase CLI Reference](https://firebase.google.com/docs/cli)
- [FIREBASE_EMULATOR_SETUP.md](./resources/js/Pages/QuizManagement/Live/FIREBASE_EMULATOR_SETUP.md) - Detailed guide

## 🆘 Need Help?

Check the comprehensive troubleshooting guide in:
`resources/js/Pages/QuizManagement/Live/FIREBASE_EMULATOR_SETUP.md`
