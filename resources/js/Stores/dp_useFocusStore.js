import { defineStore } from 'pinia';
import axios from 'axios';
import { Notify } from 'quasar';

export const useFocusStore = defineStore('dp_focus', {
    state: () => ({
        activeSession: null,
        startTime: null,
        elapsedMinutes: 0,
        timerInterval: null,
        isFocusing: false,
    }),

    actions: {
        async startFocus(dailyTaskId = null) {
            try {
                const startTime = new Date().toISOString();
                const response = await axios.post(route('dp.focus.store'), {
                    dp_daily_task_id: dailyTaskId,
                    start_time: startTime
                });
                this.activeSession = response.data;
                this.startTime = new Date();
                this.isFocusing = true;
                this.startTimer();
                Notify.create({ type: 'positive', message: 'Focus session started! 🚀' });
            } catch (error) {
                Notify.create({ type: 'negative', message: 'Failed to start focus session' });
            }
        },

        async endFocus(notes = '') {
            if (!this.activeSession) return;
            try {
                const endTime = new Date();
                const duration = Math.round((endTime - this.startTime) / 60000); // minutes
                
                await axios.put(route('dp.focus.update', this.activeSession.id), {
                    end_time: endTime.toISOString(),
                    duration_minutes: duration,
                    notes: notes
                });

                this.stopTimer();
                this.activeSession = null;
                this.isFocusing = false;
                this.elapsedMinutes = 0;
                Notify.create({ type: 'positive', message: 'Focus session ended. Great job! 🎉' });
            } catch (error) {
                Notify.create({ type: 'negative', message: 'Failed to end focus session' });
            }
        },

        async logDistraction() {
            if (!this.activeSession) return;
            try {
                await axios.post(route('dp.focus.distraction', this.activeSession.id));
                Notify.create({ type: 'warning', message: 'Distraction logged. Stay focused! 👀' });
            } catch (error) {
                console.error('Failed to log distraction');
            }
        },

        startTimer() {
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerInterval = setInterval(() => {
                if (this.startTime) {
                    const now = new Date();
                    this.elapsedMinutes = Math.floor((now - this.startTime) / 60000);
                }
            }, 60000); // Update every minute
        },

        stopTimer() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
        }
    }
});
