import { toast } from 'vue3-toastify';

export const handleAxiosError = (error, formErrors = null, options = {}) => {
    const {
        logToConsole = true,
        showToast = true,
        customMessages = {}
    } = options;

    let errorMessage = '';

    if (error.response) {
        const status = error.response.status;
        
        // Handle different status codes
        if (status === 422 && error.response.data?.errors) {
            if (formErrors) {
                formErrors.value = error.response.data.errors;
            }
            errorMessage = customMessages[422] || 'Validation failed';
        } else if (status === 404) {
            errorMessage = customMessages[404] || 'Resource not found';
        } else if (status === 403) {
            errorMessage = customMessages[403] || 'Permission denied';
        } else if (status === 401) {
            errorMessage = customMessages[401] || 'Unauthorized access';
        } else if (error.response.data?.message) {
            errorMessage = error.response.data.message;
        } else {
            errorMessage = customMessages.default || `Server error: ${status}`;
        }

        if (formErrors) {
            formErrors.value = { error: [errorMessage] };
        }
    } else if (error.request) {
        errorMessage = 'No response from server. Please check your connection.';
        if (formErrors) {
            formErrors.value = { error: [errorMessage] };
        }
    } else {
        errorMessage = 'Failed to send request. Please try again.';
        if (formErrors) {
            formErrors.value = { error: [errorMessage] };
        }
    }

    if (showToast) {
        toast.error(errorMessage);
    }

    if (logToConsole) {
        console.error('Request failed:', {
            status: error.response?.status,
            data: error.response?.data,
            message: error.message,
            errorMessage
        });
    }

    return errorMessage;
};