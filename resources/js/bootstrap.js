import axios from 'axios';

// Configure axios defaults for Sanctum SPA authentication
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;
axios.defaults.baseURL = import.meta.env.VITE_API_URL || window.location.origin;

// Convenience helper for API calls (prefix /api)
export const api = axios.create({
    withCredentials: true,
    baseURL: `${axios.defaults.baseURL}/api`,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    },
});

// Make axios available globally if needed
window.axios = axios;

export default axios;

