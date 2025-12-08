/**
 * Composable for countdown timer functionality
 * Used for timed questions and challenges
 */

import { ref, computed, onUnmounted } from 'vue';

export interface TimerState {
    timeRemaining: number; // in seconds
    isRunning: boolean;
    isPaused: boolean;
    isExpired: boolean;
}

export interface UseTimerOptions {
    duration: number; // in seconds
    onTick?: (timeRemaining: number) => void;
    onExpire?: () => void;
    autoStart?: boolean;
}

export interface UseTimerReturn {
    state: TimerState;
    start: () => void;
    pause: () => void;
    resume: () => void;
    reset: () => void;
    stop: () => void;
    addTime: (seconds: number) => void;
    progress: number; // 0-100
    formattedTime: string; // MM:SS
}

export function useTimer(options: UseTimerOptions): UseTimerReturn {
    const { duration, onTick, onExpire, autoStart = false } = options;

    const state = ref<TimerState>({
        timeRemaining: duration,
        isRunning: false,
        isPaused: false,
        isExpired: false,
    });

    let intervalId: number | null = null;

    /**
     * Start the timer
     */
    function start(): void {
        if (state.value.isRunning) return;

        state.value.isRunning = true;
        state.value.isPaused = false;
        state.value.isExpired = false;

        intervalId = window.setInterval(() => {
            if (state.value.timeRemaining > 0) {
                state.value.timeRemaining--;
                onTick?.(state.value.timeRemaining);

                if (state.value.timeRemaining === 0) {
                    state.value.isExpired = true;
                    stop();
                    onExpire?.();
                }
            }
        }, 1000);
    }

    /**
     * Pause the timer
     */
    function pause(): void {
        if (!state.value.isRunning || state.value.isPaused) return;

        state.value.isPaused = true;
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    }

    /**
     * Resume the timer
     */
    function resume(): void {
        if (!state.value.isPaused) return;

        state.value.isPaused = false;
        start();
    }

    /**
     * Reset the timer to initial duration
     */
    function reset(): void {
        stop();
        state.value.timeRemaining = duration;
        state.value.isExpired = false;
    }

    /**
     * Stop the timer
     */
    function stop(): void {
        state.value.isRunning = false;
        state.value.isPaused = false;

        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    }

    /**
     * Add time to the timer
     */
    function addTime(seconds: number): void {
        state.value.timeRemaining += seconds;
        if (state.value.timeRemaining < 0) {
            state.value.timeRemaining = 0;
        }
    }

    /**
     * Progress percentage (0-100)
     */
    const progress = computed(() => {
        return ((duration - state.value.timeRemaining) / duration) * 100;
    });

    /**
     * Formatted time string (MM:SS)
     */
    const formattedTime = computed(() => {
        const minutes = Math.floor(state.value.timeRemaining / 60);
        const seconds = state.value.timeRemaining % 60;
        return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    });

    /**
     * Auto-start if enabled
     */
    if (autoStart) {
        start();
    }

    /**
     * Cleanup on unmount
     */
    onUnmounted(() => {
        stop();
    });

    return {
        state: state.value,
        start,
        pause,
        resume,
        reset,
        stop,
        addTime,
        progress: progress.value,
        formattedTime: formattedTime.value,
    };
}
