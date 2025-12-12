import { defineStore } from 'pinia';
import axios from 'axios';
import { Notify } from 'quasar';

export const useGamificationStore = defineStore('dp_gamification', {
    state: () => ({
        points: 0,
        badges: [],
    }),

    actions: {
        async fetchGamificationData() {
            try {
                const response = await axios.get(route('dp.gamification.index'));
                this.points = response.data.points;
                this.badges = response.data.badges;
            } catch (error) {
                console.error('Error fetching gamification data:', error);
            }
        },

        async awardPoints(points, reason, badge = null) {
            try {
                await axios.post(route('dp.gamification.store'), {
                    points,
                    reason,
                    badge
                });
                this.points += points;
                if (badge) this.badges.push(badge);
                
                Notify.create({ 
                    type: 'positive', 
                    message: `+${points} Points! ${reason} 🌟`,
                    position: 'top',
                    timeout: 2000
                });
            } catch (error) {
                console.error('Failed to award points');
            }
        }
    }
});
