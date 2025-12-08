import { ref as vueRef } from 'vue';
import { ref as dbRef, onValue, off, serverTimestamp, set } from 'firebase/database';
import { database as db } from './init';
import { toast } from 'vue3-toastify';

export const connectionState = {
    isConnected: vueRef(true),
    lastConnectedAt: vueRef(null),
    reconnectAttempts: vueRef(0),
    maxReconnectAttempts: 5,
    reconnectDelay: 2000,
};

export class ConnectionManager {
    constructor() {
        this.connectionRef = dbRef(db, '.info/connected');
        this.listeners = new Set();
        this.reconnectTimer = null;
    }

    startListening() {
        this.stopListening(); // Clear any existing listeners

        onValue(this.connectionRef, (snap) => {
            const isConnected = snap.val() === true;
            connectionState.isConnected.value = isConnected;

            if (isConnected) {
                connectionState.lastConnectedAt.value = serverTimestamp();
                connectionState.reconnectAttempts.value = 0;
                this.notifyListeners('connected');
                toast.success('Connection restored');
            } else {
                this.handleDisconnection();
            }
        }, (error) => {
            console.error('Firebase connection error:', error);
            this.handleDisconnection();
        });
    }

    handleDisconnection() {
        connectionState.isConnected.value = false;
        this.notifyListeners('disconnected');
        
        if (connectionState.reconnectAttempts.value < connectionState.maxReconnectAttempts) {
            toast.error(`Connection lost. Attempting to reconnect... (${connectionState.reconnectAttempts.value + 1}/${connectionState.maxReconnectAttempts})`);
            
            clearTimeout(this.reconnectTimer);
            this.reconnectTimer = setTimeout(() => {
                connectionState.reconnectAttempts.value++;
                this.attemptReconnect();
            }, connectionState.reconnectDelay);
        } else {
            toast.error('Connection failed after multiple attempts. Please check your internet connection.');
            this.notifyListeners('failed');
        }
    }

    attemptReconnect() {
        // Force a new connection attempt
        const testRef = dbRef(db, '.info/connected');
        onValue(testRef, () => {
            off(testRef);
        }, (error) => {
            console.error('Reconnection attempt failed:', error);
            this.handleDisconnection();
        });
    }

    addListener(callback) {
        this.listeners.add(callback);
    }

    removeListener(callback) {
        this.listeners.delete(callback);
    }

    notifyListeners(status) {
        this.listeners.forEach(callback => callback(status));
    }

    stopListening() {
        off(this.connectionRef);
        clearTimeout(this.reconnectTimer);
    }
}

export const connectionManager = new ConnectionManager();