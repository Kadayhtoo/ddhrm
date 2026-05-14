import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';

const tokenKey = 'ddhrm_token';

export function getStoredToken() {
    return localStorage.getItem(tokenKey);
}

export function setStoredToken(token) {
    if (token) {
        localStorage.setItem(tokenKey, token);
    } else {
        localStorage.removeItem(tokenKey);
    }
}

window.axios.interceptors.request.use((config) => {
    const token = getStoredToken();
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});
