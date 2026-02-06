# Firebase Configuration for Production (qudratpro.com)

## Issue
The real-time question component works locally but not on production because Firebase environment variables are missing.

## Required Environment Variables

Add these to your production `.env` file on the server:

```bash
# Firebase Configuration
VITE_FIREBASE_API_KEY=your_api_key_here
VITE_FIREBASE_AUTH_DOMAIN=your_project.firebaseapp.com
VITE_FIREBASE_PROJECT_ID=your_project_id
VITE_FIREBASE_STORAGE_BUCKET=your_project.appspot.com
VITE_FIREBASE_MESSAGING_SENDER_ID=your_sender_id
VITE_FIREBASE_APP_ID=your_app_id
VITE_FIREBASE_MEASUREMENT_ID=G-XXXXXXXXXX
VITE_FIREBASE_DATABASE_URL=https://your-project-default-rtdb.firebaseio.com
```

## Critical: Enable Anonymous Authentication

The error `auth/configuration-not-found` means the Anonymous sign-in provider is disabled. Current app logic requires this for guests to view real-time questions.

1. Go to **Firebase Console** -> **Authentication** -> **Sign-in method**
2. Click **Add new provider** (or edit providers)
3. Select **Anonymous**
4. Toggle **Enable** switch
5. Click **Save**

## Where to Get These Values

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project (or create one if needed)
3. Go to **Project Settings** (gear icon)
4. Scroll to "Your apps" section
5. If no web app exists, click "Add app" and select web (</>) icon
6. Copy the configuration values from the `firebaseConfig` object

## Steps to Deploy

### On Your Local Machine:
```bash
# 1. Update .env file (never commit this!)
# Add the variables above

# 2. Rebuild assets
npm run build

# 3. Push build assets
cd public/build
git add .
git commit -m "Deploy Firebase-enabled build"
git push origin main
```

### On Production Server:
```bash
# 1. SSH into your server
ssh user@qudratpro.com

# 2. Navigate to your Laravel project
cd /path/to/your/laravel/project

# 3. Edit .env file
nano .env

# 4. Add the VITE_FIREBASE_* variables (see above)

# 5. Pull the latest build assets
cd public/build
git pull origin main

# 6. Clear Laravel cache
cd ../..
php artisan config:clear
php artisan cache:clear

# 7. Restart services (if using Supervisor/pm2)
# pm2 restart all
# OR
# sudo systemctl restart php8.2-fpm
```

## Verification

1. Open browser console on https://qudratpro.com/micro-component-test
2. Navigate to "Real-time Questions" tab
3. Check console for:
   - `✅ Firebase database initialized`
   - `✅ Listening to channel: question.rating-test-room-1`
   - Green "Firebase Connected" badge should appear

4. Submit an answer and look for:
   - `✅ API Response: {success: true, ...}`
   - `🔔 SIGNAL RECEIVED: {...}`
   - Answer appears in the display panel

## Troubleshooting

### "Firebase Not Configured" Error
- **Cause**: Environment variables missing or incorrect
- **Fix**: Double-check all `VITE_FIREBASE_*` variables in `.env`
- **Note**: Variables starting with `VITE_` must be set BEFORE running `npm run build`

### API Works But No Real-time Updates
- **Cause**: Frontend using old build without Firebase config
- **Fix**: Rebuild with `npm run build` after adding env vars

### "Permission Denied" on Firebase
- **Cause**: Firebase Realtime Database rules too restrictive
- **Fix**: Update Firebase rules in Firebase Console:
  ```json
  {
    "rules": {
      "channels": {
        "$channel_id": {
          ".read": true,
          ".write": true
        }
      }
    }
  }
  ```

## Testing Locally First

Before deploying to production, test locally:

1. Add `VITE_FIREBASE_*` to your local `.env`
2. Run `npm run dev`
3. Open http://localhost:8000/micro-component-test
4. Test the real-time questions feature
5. Open in two browser windows - changes should sync

Only deploy to production once local testing confirms Firebase works.
