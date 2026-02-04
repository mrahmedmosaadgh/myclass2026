<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    schoolSlug: String,
    branding: Object,
});

// LocalStorage keys
const STORAGE_KEYS = {
    EMAIL: 'myclass_user_email',
    SCHOOL_SLUG: 'myclass_school_slug',
};

// Get email from URL query parameter
const urlParams = new URLSearchParams(window.location.search);
const emailFromUrl = urlParams.get('email') || '';

const form = useForm({
    email: emailFromUrl || localStorage.getItem(STORAGE_KEYS.EMAIL) || '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const isLoading = ref(false);
const passwordInput = ref(null);

// Compute dynamic styles based on branding
const primaryColor = computed(() => props.branding.colors?.primary || '#6366f1');
const secondaryColor = computed(() => props.branding.colors?.secondary || '#8b5cf6');
const accentColor = computed(() => props.branding.colors?.accent || '#ec4899');
const cardStyle = computed(() => props.branding.login_page_settings?.card_style || 'glassmorphism');
const showParticles = computed(() => props.branding.login_page_settings?.show_particles ?? true);

// Set CSS variables for dynamic theming and focus password if email is pre-filled
onMounted(() => {
    document.documentElement.style.setProperty('--primary-color', primaryColor.value);
    document.documentElement.style.setProperty('--secondary-color', secondaryColor.value);
    document.documentElement.style.setProperty('--accent-color', accentColor.value);
    
    // Save school slug to localStorage
    localStorage.setItem(STORAGE_KEYS.SCHOOL_SLUG, props.schoolSlug);
    
    // Auto-focus password field if email is pre-filled
    if ((emailFromUrl || form.email) && passwordInput.value) {
        setTimeout(() => {
            passwordInput.value.focus();
        }, 100);
    }
});

const submit = () => {
    // Save email to localStorage if "Remember me" is checked
    if (form.remember) {
        localStorage.setItem(STORAGE_KEYS.EMAIL, form.email);
    } else {
        localStorage.removeItem(STORAGE_KEYS.EMAIL);
    }
    
    form.post(route('school.login.authenticate', props.schoolSlug), {
        onFinish: () => form.reset('password'),
    });
};

// Change school functionality
function changeSchool() {
    localStorage.removeItem(STORAGE_KEYS.SCHOOL_SLUG);
    window.location.href = route('login');
}
</script>

<template>
    <div class="school-login-page">
        <Head :title="branding.school_name_en">
            <link 
                rel="icon" 
                type="image/x-icon" 
                :href="branding.logo_url || '/favicon.ico'" 
            />
        </Head>

        <!-- Left Side: Form Panel -->
        <div class="form-side">
            <div class="form-content">
                <!-- Logo -->
                <div v-if="branding.logo_url" class="school-logo">
                    <img :src="branding.logo_url" :alt="branding.school_name_en" />
                </div>

                <!-- Text Header -->
                <div class="text-header">
                    <h1 class="school-name">
                        {{ branding.school_name_en }}
                    </h1>
                    <p v-if="branding.school_name_ar" class="school-name-ar">
                        {{ branding.school_name_ar }}
                    </p>
                    <p class="welcome-text">
                        Welcome back! Please login to your account.
                    </p>
                </div>

                <!-- Login Form -->
                <form @submit.prevent="submit" class="login-form">
                    <!-- Email/Username Input -->
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-user"></i>
                            Email or Username
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="text"
                            class="form-input"
                            :class="{ 'input-error': form.errors.email }"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <div v-if="form.errors.email" class="error-message">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <div class="password-input-wrapper">
                            <input
                                id="password"
                                ref="passwordInput"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                class="form-input"
                                :class="{ 'input-error': form.errors.password }"
                                required
                                autocomplete="current-password"
                            />
                            <button
                                type="button"
                                class="password-toggle"
                                @click="showPassword = !showPassword"
                            >
                                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </button>
                        </div>
                        <div v-if="form.errors.password" class="error-message">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="form-group-inline">
                        <label class="checkbox-label">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="checkbox-input"
                            />
                            <span class="checkbox-text">Remember me</span>
                        </label>
                        <a href="#" class="forgot-password">
                            Forgot password?
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="submit-button"
                        :disabled="form.processing"
                        :style="{ backgroundColor: primaryColor }"
                    >
                        <span v-if="!form.processing">
                            <i class="fas fa-sign-in-alt"></i>
                            Login
                        </span>
                        <span v-else class="loading-spinner">
                            <i class="fas fa-spinner fa-spin"></i>
                            Logging in...
                        </span>
                    </button>
                </form>

                <!-- Footer -->
                <div class="card-footer">
                    <button 
                        type="button" 
                        @click="changeSchool" 
                        class="change-school-link"
                    >
                        <i class="fas fa-exchange-alt"></i>
                        Change School
                    </button>
                </div>
            </div>
            
            <div class="form-footer-bottom">
                 <p class="footer-text">Powered by MyClass2026</p>
            </div>
        </div>

        <!-- Right Side: Visual Panel -->
        <div class="visual-side">
            <!-- Background -->
            <div 
                class="background-image"
                :style="{
                    backgroundImage: branding.background_url ? `url(${branding.background_url})` : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                }"
            ></div>

            <!-- Gradient Overlay -->
            <div 
                class="gradient-overlay"
                :style="{
                    background: `linear-gradient(135deg, ${primaryColor}DD 0%, ${secondaryColor}DD 100%)`
                }"
            ></div>

            <!-- Particles (optional) -->
            <div v-if="showParticles" class="particles">
                <div v-for="i in 50" :key="i" class="particle"></div>
            </div>
            
            <!-- Branding overlay content (optional) -->
            <div class="visual-content">
                <div class="visual-logo-container" v-if="branding.logo_url">
                    <img :src="branding.logo_url" alt="Logo" class="visual-logo" />
                </div>
                <h2 class="visual-heading" v-if="branding.school_name_en">{{ branding.school_name_en }}</h2>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Reset and Base */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Page Container - Split Layout */
.school-login-page {
    display: flex;
    width: 100vw;
    height: 100vh;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    overflow: hidden;
}

/* Form Side (Left) */
.form-side {
    width: 100%;
    max-width: 500px;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 40px;
    position: relative;
    z-index: 10;
    box-shadow: 10px 0 30px rgba(0,0,0,0.05);
}

.form-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    max-width: 400px;
    margin: 0 auto;
    width: 100%;
}

.school-logo {
    margin-bottom: 24px;
    display: flex;
    justify-content: center;
}

.school-logo img {
    height: 80px;
    width: auto;
    object-fit: contain;
}

.text-header {
    text-align: center;
    margin-bottom: 32px;
}

.school-name {
    font-size: 24px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 8px;
    line-height: 1.2;
}

.school-name-ar {
    font-size: 18px;
    font-weight: 600;
    color: #4b5563;
    margin-bottom: 12px;
}

.welcome-text {
    font-size: 14px;
    color: #6b7280;
}

/* Form Styles */
.login-form {
    display: flex;
    flex-direction: column;
    gap: 16px; /* Reduced gap */
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px; /* Reduced gap */
}

.form-label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 6px;
}

.form-input {
    width: 100%;
    padding: 12px 14px; /* Reduced padding */
    border: 1.5px solid #e5e7eb;
    border-radius: 8px; /* Slightly tighter radius */
    font-size: 14px;
    transition: all 0.2s ease;
    background: #f9fafb;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-color);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.input-error {
    border-color: #ef4444 !important;
}

.error-message {
    color: #ef4444;
    font-size: 12px;
}

.password-input-wrapper {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    font-size: 14px;
}

.password-toggle:hover {
    color: var(--primary-color);
}

.form-group-inline {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 4px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.checkbox-input {
    width: 16px;
    height: 16px;
    border-radius: 4px;
    accent-color: var(--primary-color);
}

.checkbox-text {
    font-size: 13px;
    color: #4b5563;
}

.forgot-password {
    font-size: 13px;
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
}

.submit-button {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    color: white;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.submit-button:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.loading-spinner {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Footer Section */
.card-footer {
    margin-top: 24px;
    display: flex;
    justify-content: center;
}

.change-school-link {
    background: none;
    border: none;
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: color 0.2s;
}

.change-school-link:hover {
    color: var(--primary-color);
}

.form-footer-bottom {
    text-align: center;
    padding-top: 20px;
}

.footer-text {
    font-size: 12px;
    color: #9ca3af;
}

/* Visual Side (Right) */
.visual-side {
    flex: 1;
    position: relative;
    background-color: #f3f4f6;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.background-image {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-size: cover;
    background-position: center;
}

.gradient-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    opacity: 0.9;
}

.particles {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}

.particle {
    position: absolute;
    width: 4px;
    height: 4px;
    background: rgba(255, 255, 255, 0.4);
    border-radius: 50%;
    animation: float 15s infinite;
}

.visual-content {
    position: relative;
    z-index: 5;
    text-align: center;
    color: white;
    padding: 40px;
    max-width: 600px;
}

.visual-logo-container {
    background: rgba(255, 255, 255, 0.15);
    padding: 20px;
    border-radius: 20px;
    backdrop-filter: blur(10px);
    display: inline-block;
    margin-bottom: 20px;
    border: 1px solid rgba(255,255,255,0.3);
}

.visual-logo {
    height: 100px;
    width: auto;
    filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));
}

.visual-heading {
    font-size: 32px;
    font-weight: 800;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    margin-top: 10px;
}

@keyframes float {
    0% { transform: translateY(100vh) translateX(0); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { transform: translateY(-100vh) translateX(100px); opacity: 0; }
}

/* Random particle animation delays would go here (same as before) */

/* Responsive */
@media (max-width: 900px) {
    .school-login-page {
        flex-direction: column;
    }
    
    .form-side {
        width: 100%;
        max-width: none;
        flex: 1;
        border-radius: 24px 24px 0 0;
        margin-top: -24px;
    }
    
    .visual-side {
        flex: 0.4;
        min-height: 200px;
    }
}

@media (max-width: 480px) {
    .form-side {
        padding: 24px;
    }
    
    .school-logo img {
        height: 60px;
    }
    
    .school-name {
        font-size: 20px;
    }
}
</style>
