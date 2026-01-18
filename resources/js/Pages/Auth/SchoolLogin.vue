<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    schoolSlug: String,
    branding: Object,
});

// Get email from URL query parameter
const urlParams = new URLSearchParams(window.location.search);
const emailFromUrl = urlParams.get('email') || '';

const form = useForm({
    email: emailFromUrl,
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
    
    // Auto-focus password field if email is pre-filled
    if (emailFromUrl && passwordInput.value) {
        setTimeout(() => {
            passwordInput.value.focus();
        }, 100);
    }
});

const submit = () => {
    form.post(route('school.login.authenticate', props.schoolSlug), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="school-login-page">
        <Head :title="`Login - ${branding.school_name_en}`">
            <link 
                rel="icon" 
                type="image/x-icon" 
                :href="branding.logo_url || '/favicon.ico'" 
            />
        </Head>

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

        <!-- Login Card -->
        <div class="login-container">
            <div 
                class="login-card"
                :class="`card-${cardStyle}`"
            >
                <!-- Logo -->
                <div v-if="branding.logo_url" class="school-logo">
                    <img :src="branding.logo_url" :alt="branding.school_name_en" />
                </div>

                <!-- School Name -->
                <h1 class="school-name">
                    {{ branding.school_name_en }}
                </h1>
                <p v-if="branding.school_name_ar" class="school-name-ar">
                    {{ branding.school_name_ar }}
                </p>

                <!-- Welcome Text -->
                <p class="welcome-text">
                    Welcome back! Please login to your account.
                </p>

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

                    <!-- Remember Me -->
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
                    <p class="footer-text">
                        Powered by MyClass2026
                    </p>
                </div>
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

/* Page Container */
.school-login-page {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100vw;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* Background */
.background-image {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.gradient-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1;
}

/* Particles */
.particles {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 2;
    overflow: hidden;
}

.particle {
    position: absolute;
    width: 4px;
    height: 4px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 50%;
    animation: float 15s infinite;
}

.particle:nth-child(odd) {
    animation-duration: 20s;
}

.particle:nth-child(even) {
    animation-duration: 25s;
}

@keyframes float {
    0% {
        transform: translateY(100vh) translateX(0);
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    90% {
        opacity: 1;
    }
    100% {
        transform: translateY(-100vh) translateX(100px);
        opacity: 0;
    }
}

/* Generate random positions for particles */
.particle:nth-child(1) { left: 5%; animation-delay: 0s; }
.particle:nth-child(2) { left: 15%; animation-delay: 2s; }
.particle:nth-child(3) { left: 25%; animation-delay: 4s; }
.particle:nth-child(4) { left: 35%; animation-delay: 1s; }
.particle:nth-child(5) { left: 45%; animation-delay: 3s; }
.particle:nth-child(6) { left: 55%; animation-delay: 5s; }
.particle:nth-child(7) { left: 65%; animation-delay: 2.5s; }
.particle:nth-child(8) { left: 75%; animation-delay: 4.5s; }
.particle:nth-child(9) { left: 85%; animation-delay: 1.5s; }
.particle:nth-child(10) { left: 95%; animation-delay: 3.5s; }

/* Login Container */
.login-container {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 450px;
    padding: 20px;
    /* animation: fadeInUp 0.8s ease-out; */
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Login Card Styles */
.login-card {
    border-radius: 24px;
    padding: 48px 40px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    /* animation: breathe 4s ease-in-out infinite; */
}

@keyframes breathe {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.02);
    }
}

/* Card Style Variants */
.card-glassmorphism {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.card-solid {
    background: rgba(255, 255, 255, 0.98);
}

.card-gradient {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.85) 100%);
}

/* Logo */
.school-logo {
    text-align: center;
    margin-bottom: 24px;
}

.school-logo img {
    max-width: 180px;
    max-height: 90px;
    object-fit: contain;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
}

/* School Name */
.school-name {
    font-size: 28px;
    font-weight: 700;
    text-align: center;
    margin-bottom: 8px;
    color: #1a1a1a;
}

.card-glassmorphism .school-name {
    color: #ffffff;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.school-name-ar {
    font-size: 20px;
    font-weight: 600;
    text-align: center;
    margin-bottom: 16px;
    color: #4a4a4a;
}

.card-glassmorphism .school-name-ar {
    color: rgba(255, 255, 255, 0.9);
}

/* Welcome Text */
.welcome-text {
    text-align: center;
    color: #6b7280;
    margin-bottom: 32px;
    font-size: 15px;
}

.card-glassmorphism .welcome-text {
    color: rgba(255, 255, 255, 0.8);
}

/* Form */
.login-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-label {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-glassmorphism .form-label {
    color: rgba(255, 255, 255, 0.95);
}

.form-input {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    font-size: 15px;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
}

.card-glassmorphism .form-input {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.3);
    color: #fff;
}

.card-glassmorphism .form-input::placeholder {
    color: rgba(255, 255, 255, 0.6);
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    transform: translateY(-2px);
}

.input-error {
    border-color: #ef4444 !important;
}

.error-message {
    color: #ef4444;
    font-size: 13px;
    margin-top: 4px;
}

.card-glassmorphism .error-message {
    color: #fca5a5;
}

/* Password Input */
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
    color: #6b7280;
    cursor: pointer;
    padding: 8px;
    transition: color 0.2s;
}

.card-glassmorphism .password-toggle {
    color: rgba(255, 255, 255, 0.7);
}

.password-toggle:hover {
    color: var(--primary-color);
}

/* Remember Me & Forgot Password */
.form-group-inline {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: -8px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.checkbox-input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.checkbox-text {
    font-size: 14px;
    color: #4b5563;
}

.card-glassmorphism .checkbox-text {
    color: rgba(255, 255, 255, 0.9);
}

.forgot-password {
    font-size: 14px;
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
    transition: opacity 0.2s;
}

.forgot-password:hover {
    opacity: 0.8;
}

/* Submit Button */
.submit-button {
    width: 100%;
    padding: 16px;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.submit-button:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

.submit-button:active:not(:disabled) {
    transform: translateY(0);
}

.submit-button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.loading-spinner {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Footer */
.card-footer {
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.card-glassmorphism .card-footer {
    border-top-color: rgba(255, 255, 255, 0.2);
}

.footer-text {
    text-align: center;
    font-size: 13px;
    color: #9ca3af;
}

.card-glassmorphism .footer-text {
    color: rgba(255, 255, 255, 0.6);
}

/* Responsive */
@media (max-width: 640px) {
    .login-container {
        padding: 16px;
    }

    .login-card {
        padding: 32px 24px;
    }

    .school-name {
        font-size: 24px;
    }

    .school-name-ar {
        font-size: 18px;
    }
}
</style>
