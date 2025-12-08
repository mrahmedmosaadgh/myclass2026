import { defineStore } from 'pinia';
import axios from 'axios';
import { Notify } from 'quasar';

export const useScheduleStore = defineStore('dp_schedule', {
    state: () => ({
        masterTasks: [],
        dailyTasks: [],
        currentDate: new Date().toISOString().substr(0, 10),
        loading: false,
    }),

    actions: {
        async fetchMasterSchedule() {
            this.loading = true;
            try {
                const response = await axios.get(route('dp.master.index'));
                // Assuming Inertia response, but for API calls we might need to handle differently
                // If using Inertia visit, state is passed as props. 
                // But for store management, we might want to fetch data or sync with props.
                // For now, let's assume we use this store to manage state that might be updated via API calls
                // or we rely on Inertia props passed to pages. 
                // However, for a "store", we usually want to fetch data.
                // Since the controllers return Inertia::render, we should probably rely on page props 
                // or have API endpoints that return JSON.
                // The current controllers return Inertia views. 
                // So the store might be better used for client-side state or if we change controllers to return JSON for some calls.
                // Actually, for a SPA feel, we can use Inertia router to reload data.
                
                // Let's assume we populate this store from the Page props on mount, 
                // and use actions to perform updates which then reload Inertia.
            } catch (error) {
                console.error('Error fetching master schedule:', error);
            } finally {
                this.loading = false;
            }
        },

        setMasterTasks(tasks) {
            this.masterTasks = tasks;
        },

        setDailyTasks(tasks) {
            this.dailyTasks = tasks;
        },

        async createTask(task) {
            try {
                await axios.post(route('dp.master.store'), task);
                Notify.create({ type: 'positive', message: 'Task created successfully' });
                // Reload page or fetch updated list
                // Inertia.reload() is typical usage
            } catch (error) {
                Notify.create({ type: 'negative', message: 'Failed to create task' });
            }
        },

        async updateTask(id, task) {
            try {
                await axios.put(route('dp.master.update', id), task);
                Notify.create({ type: 'positive', message: 'Task updated successfully' });
            } catch (error) {
                Notify.create({ type: 'negative', message: 'Failed to update task' });
            }
        },

        async deleteTask(id) {
            try {
                await axios.delete(route('dp.master.destroy', id));
                Notify.create({ type: 'positive', message: 'Task deleted successfully' });
            } catch (error) {
                Notify.create({ type: 'negative', message: 'Failed to delete task' });
            }
        },

        async updateDailyTaskStatus(id, status) {
            try {
                await axios.put(route('dp.daily.update', id), { status });
                Notify.create({ type: 'positive', message: 'Task status updated' });
                // Optimistic update
                const task = this.dailyTasks.find(t => t.id === id);
                if (task) task.status = status;
            } catch (error) {
                Notify.create({ type: 'negative', message: 'Failed to update status' });
            }
        }
    }
});
