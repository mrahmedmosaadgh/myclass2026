import { defineStore } from 'pinia';
import axios from 'axios';

export const useReportsStore = defineStore('dp_reports', {
    state: () => ({
        dailyStats: {},
        weeklyStats: {},
        loading: false,
    }),

    actions: {
        async fetchReports() {
            this.loading = true;
            try {
                // Since the controller returns Inertia view, we might need a JSON endpoint
                // or we rely on the page props. 
                // If we want to fetch via AJAX, we need the controller to return JSON if requested via API.
                // For now, let's assume this store is populated by page props.
                // Or we can add a header to request JSON.
                const response = await axios.get(route('dp.reports.index'), {
                    headers: { 'X-Inertia': false, 'Accept': 'application/json' } 
                });
                // Note: Inertia controllers usually return Inertia::render. 
                // To support JSON, we might need to modify controller or use a different endpoint.
                // But for this project, let's assume we pass data via props to the page component
                // and initialize the store from there.
            } catch (error) {
                console.error('Error fetching reports:', error);
            } finally {
                this.loading = false;
            }
        },

        setStats(daily, weekly) {
            this.dailyStats = daily;
            this.weeklyStats = weekly;
        }
    }
});
