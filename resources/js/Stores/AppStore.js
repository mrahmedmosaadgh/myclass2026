import { defineStore } from 'pinia'
import axios from 'axios'
import NProgress from 'nprogress'
import { Notify } from 'quasar'

export const useAppStore = defineStore('app', {
    state: () => ({
        loading: false,
        error: null,
        res: {},
        selected_school: {},
        selectedSchool_data: {},
    }),

    actions: {
        fetchData({
            endpoint,
            method = 'get',
            params = null,
            data = null,
            onBefore = () => {},
            onSuccess = () => {},
            onError = () => {},
            successMessage = 'Operation completed successfully!',
            errorMessage = 'Operation failed'
        } = {}) {
            // Validate endpoint
            if (!endpoint) {
                const message = 'Endpoint is required'
                Notify.create({
                    type: 'negative',
                    message,
                    position: 'bottom-right'
                })
                return Promise.reject(message)
            }

            this.loading = true
            this.error = null
            onBefore()
            NProgress.start()

            // Get CSRF token from meta tag
            const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.content;

            const config = {
                method,
                url: endpoint,
                ...(params && { params }),
                ...(data && { data }),
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                withCredentials: true
            }

            return axios(config)
                .then(response => {
                    NProgress.done()
                    Notify.create({
                        type: 'positive',
                        message: response.data?.message || successMessage,
                        position: 'bottom-right'
                    })
                    onSuccess(response.data)
                    return response.data
                })
                .catch(err => {
                    NProgress.done()
                    console.error("Request failed:", err)
                    this.error = err.response?.data?.message || errorMessage
                    Notify.create({
                        type: 'negative',
                        message: this.error,
                        position: 'bottom-right'
                    })
                    onError(err)
                    return null
                })
                .finally(() => {
                    this.loading = false
                })
        }
    }
})
