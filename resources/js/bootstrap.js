import axios from 'axios';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common.Accept = 'application/json';
window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;

function readCookie(name) {
    const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));

    return match ? decodeURIComponent(match[1]) : '';
}

window.axios.interceptors.request.use((config) => {
    const xsrf = readCookie('XSRF-TOKEN');

    if (xsrf) {
        config.headers['X-XSRF-TOKEN'] = xsrf;
    }

    return config;
});

window.axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        const config = error.config;

        if (error.response?.status === 419 && config && !config.__csrfRetry) {
            config.__csrfRetry = true;
            await window.axios.get('/sanctum/csrf-cookie');

            return window.axios.request(config);
        }

        return Promise.reject(error);
    },
);
