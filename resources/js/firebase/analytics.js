import { getAnalytics } from 'firebase/analytics';
import { app } from './init';

let analytics = null;

// Only initialize analytics in browser environment and when enabled
if (typeof window !== 'undefined' && import.meta.env.VITE_FIREBASE_ANALYTICS_ENABLED !== 'false' && app) {
  try {
    // Check if analytics is already initialized
    if (window.firebaseAnalytics) {
      analytics = window.firebaseAnalytics;
      console.log('Using existing Firebase Analytics instance');
    } else {
      analytics = getAnalytics(app);
      window.firebaseAnalytics = analytics;
      console.log('Firebase Analytics initialized');
    }
  } catch (error) {
    console.warn('Firebase Analytics initialization failed:', error);
  }
}

export { analytics };
