<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

// LocalStorage keys
const STORAGE_KEYS = {
    EMAIL: 'myclass_user_email',
    SCHOOL_SLUG: 'myclass_school_slug',
};

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const detectingSchool = ref(false);
const savedSchoolName = ref('');

// Check for saved school on mount
onMounted(async () => {
    // Try to get saved email
    const savedEmail = localStorage.getItem(STORAGE_KEYS.EMAIL);
    if (savedEmail) {
        form.email = savedEmail;
    }

    // Try to get saved school slug
    const savedSlug = localStorage.getItem(STORAGE_KEYS.SCHOOL_SLUG);
    if (savedSlug) {
        // Validate the saved school slug
        try {
            const response = await axios.post(route('validate.school'), {
                slug: savedSlug
            });
            
            if (response.data.valid) {
                // Build redirect URL with email and preserve intended URL
                let redirectUrl = response.data.login_url;
                const params = new URLSearchParams();
                
                if (savedEmail) {
                    params.append('email', savedEmail);
                }
                
                // Preserve intended URL from current query string
                const currentParams = new URLSearchParams(window.location.search);
                const intendedUrl = currentParams.get('intended');
                if (intendedUrl) {
                    params.append('intended', intendedUrl);
                }
                
                if (params.toString()) {
                    redirectUrl += '?' + params.toString();
                }
                
                // Redirect to school-specific login
                window.location.href = redirectUrl;
            } else {
                // Invalid school, clear localStorage
                localStorage.removeItem(STORAGE_KEYS.SCHOOL_SLUG);
            }
        } catch (error) {
            // School not found, clear localStorage
            localStorage.removeItem(STORAGE_KEYS.SCHOOL_SLUG);
        }
    }
});

// Detect user's school when email is entered
async function detectSchool() {
    if (!form.email || form.email.length < 3) return;
    
    detectingSchool.value = true;
    
    try {
        const response = await axios.post(route('detect.school'), {
            email: form.email
        });
        
        if (response.data.redirect) {
            // Save email to localStorage
            localStorage.setItem(STORAGE_KEYS.EMAIL, form.email);
            
            // Save school slug to localStorage
            localStorage.setItem(STORAGE_KEYS.SCHOOL_SLUG, response.data.school_slug);
            
            // Build redirect URL with email and preserve intended URL
            let redirectUrl = response.data.redirect;
            const params = new URLSearchParams();
            params.append('email', form.email);
            
            // Preserve intended URL from current query string
            const currentParams = new URLSearchParams(window.location.search);
            const intendedUrl = currentParams.get('intended');
            if (intendedUrl) {
                params.append('intended', intendedUrl);
            }
            
            redirectUrl += '?' + params.toString();
            
            // Redirect to school-specific login page
            window.location.href = redirectUrl;
        }
    } catch (error) {
        // User not found or no school - continue with default login
        console.log('No school detected, using default login');
    } finally {
        detectingSchool.value = false;
    }
}

// Clear saved school (for "Change School" functionality)
function clearSchool() {
    localStorage.removeItem(STORAGE_KEYS.SCHOOL_SLUG);
    savedSchoolName.value = '';
}

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />
                <!-- type="email" -->
                <TextInput
                    id="email"
                    v-model="form.email"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                    @blur="detectSchool"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="!detectingSchool" class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div v-if="!detectingSchool" class="block mt-4">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">Remember me</span>
                </label>
            </div>

            <div v-if="!detectingSchool" class="flex items-center justify-end mt-4">
                <Link v-if="canResetPassword" :href="route('password.request')" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Forgot your password?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Log in
                </PrimaryButton>
            </div>
            
            <div v-if="detectingSchool" class="mt-4 text-center text-sm text-gray-600">
                Detecting your school...
            </div>
        </form>
    </AuthenticationCard>
</template>
