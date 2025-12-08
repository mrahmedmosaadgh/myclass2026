# 🚀 Firebase Emulator - Quick Start

## ⚡ Super Quick Setup (3 Steps)

### 1️⃣ Install Firebase CLI
```bash
npm install -g firebase-tools
```

### 2️⃣ Login to Firebase
```bash
firebase login
```

### 3️⃣ Start Everything
```bash
# Option A: Use the batch script (Windows)
start-dev.bat

# Option B: Manual start
firebase emulators:start
```

## ✅ Verify It's Working

Open browser console at `http://localhost:8000/quiz/live/teacher`

You should see:
```
🔧 Development mode detected - connecting to Firebase emulators
✅ Connected to Auth Emulator at http://localhost:9099
✅ Signed in anonymously to Firebase
```

## 🎯 Access Points

| Service | URL |
|---------|-----|
| **Emulator UI** | http://localhost:4000 |
| **Teacher Page** | http://localhost:8000/quiz/live/teacher |
| **Student Page** | http://localhost:8000/quiz/live/join |

## 🛠️ Common Commands

```bash
# Start emulators
firebase emulators:start

# Start only specific emulators
firebase emulators:start --only auth

# Check Firebase CLI version
firebase --version

# Login to Firebase
firebase login

# Logout from Firebase
firebase logout
```

## 🐛 Quick Troubleshooting

| Problem | Solution |
|---------|----------|
| `firebase: command not found` | Install Firebase CLI: `npm install -g firebase-tools` |
| Port already in use | Stop other services or change ports in `firebase.json` |
| Still seeing 400 errors | Make sure emulators are running and you're on `localhost` |
| Can't access emulator UI | Check if port 4000 is available |

## 📁 Files Created

- ✅ `firebase.json` - Emulator configuration
- ✅ `.firebaserc` - Project configuration  
- ✅ `start-dev.bat` - Quick start script (Windows)
- ✅ `setup-firebase-emulator.md` - Detailed instructions

## 🎓 Next Steps

1. **First time?** Read `setup-firebase-emulator.md`
2. **Need details?** Check `resources/js/Pages/QuizManagement/Live/FIREBASE_EMULATOR_SETUP.md`
3. **Ready to code?** Run `start-dev.bat` and start developing!

## 💡 Pro Tips

- Keep emulators running while developing
- Use Emulator UI (port 4000) to inspect auth and database
- Emulator data is cleared when you restart
- Production Firebase is automatically used when not on localhost

---

**That's it! You're ready to develop with Firebase Emulators! 🎉**
