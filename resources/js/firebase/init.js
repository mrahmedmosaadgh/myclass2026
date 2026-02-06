import { initializeApp } from 'firebase/app';
import { getAuth, connectAuthEmulator, signInAnonymously } from 'firebase/auth';
import { getDatabase, connectDatabaseEmulator } from 'firebase/database';
import { ToolsSwitcher } from '@/Utils/toolsSwitcher';

// Initialize variables
let app = null;
let auth = null;
let database = null;

// Check if Firebase is enabled
if (!ToolsSwitcher.isFirebaseEnabled()) {
  console.log('🚫 Firebase disabled by toolsSwitcher');
  // Variables remain null
} else {
  console.log('🔥 Firebase enabled by toolsSwitcher');

  const firebaseConfig = {
    apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
    authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
    projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
    storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
    messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
    appId: import.meta.env.VITE_FIREBASE_APP_ID,
    measurementId: import.meta.env.VITE_FIREBASE_MEASUREMENT_ID,
    databaseURL: import.meta.env.VITE_FIREBASE_DATABASE_URL
  };

  app = initializeApp(firebaseConfig);
  auth = ToolsSwitcher.isEnabled('firebase', 'auth') ? getAuth(app) : null;
  database = ToolsSwitcher.isEnabled('firebase', 'database') ? getDatabase(app) : null;

  // Connect to emulators only if enabled
  const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
  const isLocalIp = window.location.hostname.startsWith('192.168.');

  if (ToolsSwitcher.isEmulatorsEnabled() && (isLocalhost || isLocalIp) && !window.location.hostname.includes('qudratpro.com')) {
    console.log('🔧 Emulators enabled - connecting...');

    try {
      if (auth) {
        connectAuthEmulator(auth, 'http://127.0.0.1:9099');
        console.log('✅ Connected to Auth Emulator');
      }

      if (database) {
        connectDatabaseEmulator(database, '127.0.0.1', 9000);
        console.log('✅ Connected to Database Emulator');
      }
    } catch (error) {
      console.warn('⚠️ Emulator connection error:', error.message);
    }
  }

  // Anonymous sign-in only if auth is enabled
  if (auth && ToolsSwitcher.isEnabled('firebase', 'auth')) {
    signInAnonymously(auth)
      .then(() => console.log('✅ Signed in anonymously'))
      .catch(error => console.error('❌ Anonymous sign-in failed:', error));
  }
}

// Export at top level
export { app, auth, database };

