/**
 * CSRF Token Management Utility
 *
 * This utility provides functions to manage CSRF tokens in Laravel applications
 * to prevent token mismatch errors, especially after login or long sessions.
 */

/**
 * Get the current CSRF token from the meta tag
 * @returns {string|null} The CSRF token or null if not found
 */
export const getCsrfToken = () => {
    const token = document.head.querySelector('meta[name="csrf-token"]');
    return token ? token.content : null;
};

/**
 * Set the CSRF token in axios default headers
 * @param {string} token - The CSRF token to set
 */
export const setCsrfTokenInAxios = (token = null) => {
    const csrfToken = token || getCsrfToken();
    if (csrfToken && window.axios) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    }
};

/**
 * Refresh the CSRF token by getting it from the meta tag and updating axios
 */
export const refreshCsrfToken = async () => {
    // First try to get token from meta tag
    let token = getCsrfToken();

    if (!token) {
        // If no token in meta tag, try to fetch a fresh one from server
        try {
            const response = await fetch('/sanctum/csrf-cookie', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                // Wait a moment for the meta tag to be updated
                await new Promise(resolve => setTimeout(resolve, 100));
                token = getCsrfToken();
            }
        } catch (error) {
            console.error('Failed to fetch CSRF cookie:', error);
        }
    }

    if (token) {
        setCsrfTokenInAxios(token);
        console.log('CSRF token refreshed:', token.substring(0, 10) + '...');
        return token;
    } else {
        console.error('Failed to refresh CSRF token - no token available');
        return null;
    }
};

/**
 * Setup axios interceptors to handle CSRF tokens automatically
 * @param {Object} axiosInstance - The axios instance to configure (optional, defaults to window.axios)
 */
export const setupCsrfInterceptors = (axiosInstance = null) => {
    const axios = axiosInstance || window.axios;

    if (!axios) {
        console.error('Axios instance not found');
        return;
    }

    // Request interceptor - always add fresh CSRF token
    axios.interceptors.request.use(
        (config) => {
            const token = getCsrfToken();
            if (token) {
                config.headers['X-CSRF-TOKEN'] = token;
            }
            config.headers['X-Requested-With'] = 'XMLHttpRequest';
            return config;
        },
        (error) => {
            return Promise.reject(error);
        }
    );

    // Response interceptor - handle CSRF errors
    axios.interceptors.response.use(
        (response) => {
            return response;
        },
        (error) => {
            if (error.response?.status === 419) {
                const originalRequest = error.config;

                // Don't retry login, logout, or register requests to avoid infinite loops
                const authEndpoints = ['/login', '/logout', '/register', '/password/reset'];
                const isAuthEndpoint = authEndpoints.some(endpoint =>
                    originalRequest.url?.includes(endpoint)
                );

                if (isAuthEndpoint) {
                    console.warn('CSRF token mismatch on auth endpoint. Reloading page...');
                    window.location.reload();
                    return Promise.reject(error);
                }

                // Prevent infinite retry loops
                if (originalRequest._retryCount >= 1) {
                    console.error('CSRF token retry limit reached. Reloading page...');
                    window.location.reload();
                    return Promise.reject(error);
                }

                console.warn('CSRF token mismatch detected. Attempting to refresh...');

                // Try to refresh the token (async)
                return refreshCsrfToken().then(newToken => {
                    if (newToken) {
                        // Mark this as a retry attempt
                        originalRequest._retryCount = (originalRequest._retryCount || 0) + 1;
                        originalRequest.headers['X-CSRF-TOKEN'] = newToken;

                        console.log('Retrying request with new CSRF token...');
                        return axios.request(originalRequest);
                    } else {
                        // If we can't get a new token, reload the page
                        console.error('Could not refresh CSRF token. Reloading page...');
                        window.location.reload();
                        return Promise.reject(error);
                    }
                }).catch(refreshError => {
                    console.error('Error during CSRF token refresh:', refreshError);
                    window.location.reload();
                    return Promise.reject(error);
                });
            }
            return Promise.reject(error);
        }
    );
};

/**
 * Validate that the CSRF token exists and is not empty
 * @returns {boolean} True if token exists and is valid
 */
export const validateCsrfToken = () => {
    const token = getCsrfToken();
    return token && token.length > 0;
};

/**
 * Get CSRF token for manual form submissions
 * @returns {Object} Object with token name and value for form submission
 */
export const getCsrfTokenForForm = () => {
    const token = getCsrfToken();
    return {
        _token: token,
        'X-CSRF-TOKEN': token
    };
};

/**
 * Add CSRF token to FormData object
 * @param {FormData} formData - The FormData object to add the token to
 * @returns {FormData} The FormData object with CSRF token added
 */
export const addCsrfToFormData = (formData) => {
    const token = getCsrfToken();
    if (token) {
        formData.append('_token', token);
    }
    return formData;
};

/**
 * Create a fetch request with CSRF token included
 * @param {string} url - The URL to fetch
 * @param {Object} options - Fetch options
 * @returns {Promise} Fetch promise with CSRF token included
 */
export const fetchWithCsrf = (url, options = {}) => {
    const token = getCsrfToken();

    const defaultOptions = {
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            ...options.headers
        },
        credentials: 'same-origin',
        ...options
    };

    return fetch(url, defaultOptions);
};

/**
 * Setup Inertia.js CSRF token handling
 */
export const setupInertiaCSRF = () => {
    // Import Inertia router dynamically to avoid circular dependencies
    import('@inertiajs/vue3').then(({ router }) => {
        // Add CSRF token to all Inertia requests
        router.on('before', (event) => {
            const token = getCsrfToken();
            if (token) {
                // Ensure headers object exists
                if (!event.detail.visit.headers) {
                    event.detail.visit.headers = {};
                }
                event.detail.visit.headers['X-CSRF-TOKEN'] = token;
                event.detail.visit.headers['X-Requested-With'] = 'XMLHttpRequest';
            }
        });

        // Handle CSRF token mismatch errors
        router.on('error', (errors) => {
            // Check if it's a CSRF token mismatch (419 error)
            if (errors.response?.status === 419) {
                console.log('CSRF token mismatch on auth endpoint. Reloading page...');
                // Refresh the page to get a new CSRF token
                window.location.reload();
            }
        });

        // Handle successful responses to update token if needed
        router.on('success', (event) => {
            // Check if the response includes a new CSRF token
            const newToken = event.detail.page.props?.csrf_token;
            if (newToken) {
                const metaTag = document.head.querySelector('meta[name="csrf-token"]');
                if (metaTag && metaTag.content !== newToken) {
                    metaTag.content = newToken;
                    console.log('CSRF token refreshed:', newToken.substring(0, 10) + '...');
                }
            }
        });

        console.log('Inertia CSRF protection initialized');
    }).catch(error => {
        console.warn('Could not setup Inertia CSRF protection:', error);
    });
};

/**
 * Initialize CSRF protection for the entire application
 * Call this once in your main app.js file
 */
export const initializeCsrfProtection = () => {
    // Set initial token
    refreshCsrfToken();

    // Setup interceptors
    setupCsrfInterceptors();

    // Setup Inertia CSRF handling
    setupInertiaCSRF();

    // Add global function for manual token refresh
    window.refreshCsrfToken = refreshCsrfToken;
    window.getCsrfToken = getCsrfToken;

    console.log('CSRF protection initialized');
};

// Auto-initialize if this file is imported
if (typeof window !== 'undefined') {
    // Make functions available globally for debugging
    window.csrfUtils = {
        getCsrfToken,
        refreshCsrfToken,
        validateCsrfToken,
        setupCsrfInterceptors
    };
}
