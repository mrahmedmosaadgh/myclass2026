/**
 * Composable for audio recording using Web Audio API
 * Supports browser-based microphone recording with playback
 */

import { ref, onUnmounted } from 'vue';

export interface RecordingState {
    isRecording: boolean;
    isPaused: boolean;
    duration: number;
    blob: Blob | null;
    url: string | null;
}

export interface UseRecordingReturn {
    state: RecordingState;
    startRecording: () => Promise<void>;
    stopRecording: () => Promise<Blob | null>;
    pauseRecording: () => void;
    resumeRecording: () => void;
    resetRecording: () => void;
    isSupported: boolean;
}

export function useRecording(): UseRecordingReturn {
    const state = ref<RecordingState>({
        isRecording: false,
        isPaused: false,
        duration: 0,
        blob: null,
        url: null,
    });

    let mediaRecorder: MediaRecorder | null = null;
    let audioChunks: Blob[] = [];
    let stream: MediaStream | null = null;
    let durationInterval: number | null = null;
    let startTime: number = 0;
    let pausedDuration: number = 0;

    const isSupported = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);

    /**
     * Start recording audio from microphone
     */
    async function startRecording(): Promise<void> {
        if (!isSupported) {
            throw new Error('Audio recording is not supported in this browser');
        }

        try {
            // Request microphone access
            stream = await navigator.mediaDevices.getUserMedia({ audio: true });

            // Reset state
            audioChunks = [];
            state.value.duration = 0;
            pausedDuration = 0;

            // Create MediaRecorder
            const options: MediaRecorderOptions = {};

            // Try to use the best available format
            if (MediaRecorder.isTypeSupported('audio/webm')) {
                options.mimeType = 'audio/webm';
            } else if (MediaRecorder.isTypeSupported('audio/mp4')) {
                options.mimeType = 'audio/mp4';
            } else if (MediaRecorder.isTypeSupported('audio/ogg')) {
                options.mimeType = 'audio/ogg';
            }

            mediaRecorder = new MediaRecorder(stream, options);

            // Handle data available
            mediaRecorder.ondataavailable = (event) => {
                if (event.data.size > 0) {
                    audioChunks.push(event.data);
                }
            };

            // Handle recording stop
            mediaRecorder.onstop = () => {
                const blob = new Blob(audioChunks, { type: mediaRecorder?.mimeType || 'audio/webm' });
                state.value.blob = blob;
                state.value.url = URL.createObjectURL(blob);

                // Stop all tracks
                stream?.getTracks().forEach(track => track.stop());
            };

            // Start recording
            mediaRecorder.start();
            state.value.isRecording = true;
            startTime = Date.now();

            // Start duration counter
            durationInterval = window.setInterval(() => {
                if (!state.value.isPaused) {
                    state.value.duration = Math.floor((Date.now() - startTime - pausedDuration) / 1000);
                }
            }, 100);

        } catch (error) {
            console.error('Failed to start recording:', error);
            throw new Error('Failed to access microphone. Please grant permission and try again.');
        }
    }

    /**
     * Stop recording and return the audio blob
     */
    async function stopRecording(): Promise<Blob | null> {
        if (!mediaRecorder || state.value.isRecording === false) {
            return null;
        }

        return new Promise((resolve) => {
            if (mediaRecorder) {
                mediaRecorder.onstop = () => {
                    const blob = new Blob(audioChunks, { type: mediaRecorder?.mimeType || 'audio/webm' });
                    state.value.blob = blob;
                    state.value.url = URL.createObjectURL(blob);
                    state.value.isRecording = false;
                    state.value.isPaused = false;

                    // Stop all tracks
                    stream?.getTracks().forEach(track => track.stop());

                    // Clear interval
                    if (durationInterval) {
                        clearInterval(durationInterval);
                        durationInterval = null;
                    }

                    resolve(blob);
                };

                mediaRecorder.stop();
            } else {
                resolve(null);
            }
        });
    }

    /**
     * Pause recording
     */
    function pauseRecording(): void {
        if (mediaRecorder && state.value.isRecording && !state.value.isPaused) {
            mediaRecorder.pause();
            state.value.isPaused = true;
            pausedDuration += Date.now() - startTime;
        }
    }

    /**
     * Resume recording
     */
    function resumeRecording(): void {
        if (mediaRecorder && state.value.isRecording && state.value.isPaused) {
            mediaRecorder.resume();
            state.value.isPaused = false;
            startTime = Date.now();
        }
    }

    /**
     * Reset recording state
     */
    function resetRecording(): void {
        if (state.value.isRecording) {
            stopRecording();
        }

        // Revoke old URL
        if (state.value.url) {
            URL.revokeObjectURL(state.value.url);
        }

        state.value = {
            isRecording: false,
            isPaused: false,
            duration: 0,
            blob: null,
            url: null,
        };

        audioChunks = [];
    }

    /**
     * Cleanup on unmount
     */
    onUnmounted(() => {
        if (state.value.isRecording) {
            stopRecording();
        }

        if (state.value.url) {
            URL.revokeObjectURL(state.value.url);
        }

        if (durationInterval) {
            clearInterval(durationInterval);
        }
    });

    return {
        state: state.value,
        startRecording,
        stopRecording,
        pauseRecording,
        resumeRecording,
        resetRecording,
        isSupported,
    };
}
