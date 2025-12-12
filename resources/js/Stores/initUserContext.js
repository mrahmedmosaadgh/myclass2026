/**
 * User Context Store Initialization Helper
 * 
 * This helper automatically initializes the user context store when the app starts.
 * It ensures the store is ready before any components try to use it.
 * 
 * @author Education Management System
 * @version 1.0.0
 */

import { useUserContextStore } from './userContextStore.js';

/**
 * Initialize user context store
 * Call this in your main app.js after Pinia is set up
 * 
 * @returns {Promise<void>}
 */
export const initializeUserContext = async () => {
    try {
        console.log('🔧 Initializing User Context System...');
        
        const userContextStore = useUserContextStore();
        await userContextStore.initialize();
        
        console.log('✅ User Context System ready');
        
        // Return the store instance for immediate use if needed
        return userContextStore;
        
    } catch (error) {
        console.error('❌ Failed to initialize User Context System:', error);
        throw error;
    }
};

/**
 * Auto-initialize when imported (optional)
 * Uncomment the line below if you want automatic initialization
 */
// initializeUserContext();

export default initializeUserContext;
