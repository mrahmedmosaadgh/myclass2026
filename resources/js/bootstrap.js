import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { initializeApp } from 'firebase/app';
import { getDatabase } from 'firebase/database';
import { initializeCsrfProtection } from './Utils/csrf.js';

window.axios = axios;

// Set common headers
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Initialize comprehensive CSRF protection
initializeCsrfProtection();

// Initialize Laravel Echo if Pusher key is available
window.Pusher = Pusher;

try {
    const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;
    const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER;

    if (pusherKey && pusherCluster) {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: pusherKey,
            cluster: pusherCluster,
            forceTLS: true
        });
        console.log('Laravel Echo initialized successfully');
    } else {
        console.warn('Pusher configuration is incomplete. Real-time messaging will not work.');
        // Create a dummy Echo object to prevent errors
        window.Echo = {
            private: () => ({ listen: () => {} }),
            join: () => ({ listen: () => {}, here: () => {}, joining: () => {}, leaving: () => {} }),
            leave: () => {}
        };
    }
} catch (error) {
    console.error('Error initializing Laravel Echo:', error);
    // Create a dummy Echo object to prevent errors
    window.Echo = {
        private: () => ({ listen: () => {} }),
        join: () => ({ listen: () => {}, here: () => {}, joining: () => {}, leaving: () => {} }),
        leave: () => {}
    };
}

// Import Firebase from our init.js file instead of initializing it here
try {
    // Import the Firebase initialization from our centralized init.js file
    import('./firebase/init.js').then(module => {
        // The Firebase app and database are already exported from init.js
        // and should be available globally via window.firebaseApp and window.firebaseDatabase
        console.log('Firebase imported from init.js in bootstrap.js');
    }).catch(error => {
        console.error('Error importing Firebase from init.js:', error);
    });
} catch (error) {
    console.error('Error setting up Firebase:', error);
}
