import { ref } from 'vue';

/**
 * useBMFirebase
 * 
 * Provides real-time syncing capabilities for the Basic Math platform
 * using the project's existing Firebase configuration.
 * All data lives under the /bm_* namespace.
 */
export function useBMFirebase() {
    const isSyncing = ref(false);
    const syncError = ref(null);

    /**
     * Pushes a completed assessment score to the real-time database.
     * Path: /bm_scores/{userId}/{assessmentId}
     */
    const pushLiveScore = async (userId, assessmentId, scoreData) => {
        isSyncing.value = true;
        try {
            // Note: Assumes global Firebase app is already initialized in the main project layout
            // const db = getDatabase();
            // await set(ref(db, `bm_scores/${userId}/${assessmentId}`), {
            //     score: scoreData.final_score,
            //     level: scoreData.level,
            //     timestamp: Date.now()
            // });
            console.log(`[Firebase Mock] Pushed score for user ${userId} under /bm_scores`);
        } catch (e) {
            syncError.value = e.message;
        } finally {
            isSyncing.value = false;
        }
    };

    /**
     * Syncs live assessment progress for the teacher dashboard to view
     * students taking the test in real-time.
     * Path: /bm_sessions/{assessmentId}
     */
    const syncActiveSession = (assessmentId, questionIndex, isComplete) => {
        // Implementation for real-time heartbeat here
        console.log(`[Firebase Mock] Syncing session ${assessmentId} at Question ${questionIndex}`);
    };

    /**
     * Subscribes to the global or classroom leaderboard.
     * Path: /bm_leaderboard/{period} (e.g., weekly)
     */
    const getLeaderboard = (period = 'weekly', callback) => {
        // Implementation for onValue() Firebase real-time listener
        // callback(snapshot.val());
        console.log(`[Firebase Mock] Subscribed to leaderboard for ${period}`);
        
        // Mock data
        callback([
            { name: 'Alice', score: 95 },
            { name: 'Bob', score: 82 },
            { name: 'Charlie', score: 76 }
        ]);

        // Return unsubscribe function
        return () => console.log('[Firebase Mock] Unsubscribed from leaderboard');
    };

    return {
        isSyncing,
        syncError,
        pushLiveScore,
        syncActiveSession,
        getLeaderboard
    };
}
