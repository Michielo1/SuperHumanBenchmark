/* API helper - provides a canonical base and a small fetch wrapper */

(function (global) {
    'use strict';

    function apiBase() {
        if (typeof global !== 'undefined' && global.API_BASE_URL) {
            return global.API_BASE_URL;
        }
        // sensible fallback used previously in some pages
        return ['pages', 'tests'].some(d => location.pathname.split('/').includes(d)) ? '../api/' : 'api/';
    }

    async function apiFetch(path, opts) {
        const base = apiBase();
        const p = (path || '').replace(/^\/+/, '');
        return fetch(base + p, opts);
    }

    // Expose to global scope
    global.apiBase = apiBase;
    global.apiFetch = apiFetch;

})(window);
