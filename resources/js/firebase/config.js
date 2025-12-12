// Import the functions you need from the SDKs you need
import { getApps, getApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";
import { getDatabase } from "firebase/database";
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries

// Use the existing Firebase app instead of initializing a new one
let app;
try {
  // Check if Firebase app is already initialized
  if (getApps().length > 0) {
    app = getApp();
    console.log('Using existing Firebase app in config.js');
  } else {
    // If not initialized, import the app from init.js
    console.log('No Firebase app found, importing from init.js');
    import('./init.js').then(module => {
      app = module.app;
    });
  }
} catch (error) {
  console.error('Error getting Firebase app:', error);
  // Fall back to window.firebaseApp if available
  if (typeof window !== 'undefined' && window.firebaseApp) {
    app = window.firebaseApp;
    console.log('Using window.firebaseApp');
  }
}

// Initialize analytics and database
let analytics = null;
let db = null;

// Only initialize if app is available
if (app) {
  try {
    analytics = getAnalytics(app);
    db = getDatabase(app);
  } catch (error) {
    console.error('Error initializing Firebase services:', error);
  }
}

export { app, analytics, db };

