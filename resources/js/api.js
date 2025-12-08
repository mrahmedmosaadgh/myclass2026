import axios from 'axios';

const api = axios.create({
    // baseURL: '',
    // baseURL: 'api',
    // baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    },
    withCredentials: true  // Important for sending cookies
});

export default api;
