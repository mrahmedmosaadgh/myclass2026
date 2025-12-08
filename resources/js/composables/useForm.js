import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

export default function useForm(initialData = {}) {
    const form = ref({ ...initialData });
    const errors = ref({});
    const processing = ref(false);

    const reset = () => {
        form.value = { ...initialData };
        errors.value = {};
        processing.value = false;
    };

    const submit = async (method, url, options = {}) => {
        processing.value = true;
        errors.value = {};

        try {
            await router[method](url, form.value, {
                preserveScroll: true,
                preserveState: true,
                ...options
            });
            
            if (options.onSuccess) {
                options.onSuccess();
            }
        } catch (error) {
            if (error.response?.data?.errors) {
                errors.value = error.response.data.errors;
            }
            
            if (options.onError) {
                options.onError(error);
            }
        } finally {
            processing.value = false;
        }
    };

    const post = (url, options) => submit('post', url, options);
    const put = (url, options) => submit('put', url, options);
    const patch = (url, options) => submit('patch', url, options);
    const destroy = (url, options) => submit('delete', url, options);

    return {
        form,
        errors,
        processing,
        reset,
        post,
        put,
        patch,
        delete: destroy
    };
}