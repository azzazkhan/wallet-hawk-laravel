import axios from 'axios';

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */
window.axios = axios.create({
    baseURL: import.meta.env.VITE_APP_URL + '/api/v1',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'Access-Control-Allow-origin': '*'
    }
});
