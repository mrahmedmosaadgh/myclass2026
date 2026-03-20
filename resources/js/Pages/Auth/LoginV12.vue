<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: String,
});

// LocalStorage key for remembering email
const STORAGE_KEYS = {
    EMAIL: 'myclass_user_email',
};

const form = useForm({
    email: localStorage.getItem(STORAGE_KEYS.EMAIL) || '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const passwordInput = ref(null);

onMounted(() => {
    if (form.email && passwordInput.value) {
        setTimeout(() => {
            passwordInput.value.focus();
        }, 100);
    }
});

const submit = () => {
    if (form.remember) {
        localStorage.setItem(STORAGE_KEYS.EMAIL, form.email);
    } else {
        localStorage.removeItem(STORAGE_KEYS.EMAIL);
    }
    
    // We post to the safe preview route
    form.post(route('login.v12.post'), {
        onFinish: () => form.reset('password'),
    });
};

</script>

<template>
    <div class="login-v12-page" :dir="$page.props.locale === 'ar' ? 'rtl' : 'ltr'">
        <Head title="Log in" />

        <!-- Split Layout Wrapper -->
        <div class="split-container">
            
            <!-- Left Side: Interactive Form Panel -->
            <div class="form-panel">
                <div class="form-content">
                    
                    <div class="brand-header">
                        <div class="logo-box">
                            <!-- Placeholder icon for logo, replace with actual logo later -->
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h1 class="brand-name">MyClass<span class="highlight">2026</span></h1>
                        <p class="welcome-text">Welcome back! Please enter your details.</p>
                    </div>

                    <div v-if="status" class="status-message">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="premium-form">
                        
                        <!-- Email Input -->
                        <div class="input-group" :class="{ 'has-error': form.errors.email, 'is-focused': form.email }">
                            <label for="email">
                                <i class="fas fa-user-circle"></i> Email or Username
                            </label>
                            <div class="input-wrapper">
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="text"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="Enter your email or username"
                                    :class="{ 'shake-anim': form.errors.email }"
                                />
                            </div>
                            <span v-if="form.errors.email" class="error-text">
                                <i class="fas fa-exclamation-circle"></i> {{ form.errors.email }}
                            </span>
                        </div>

                        <!-- Password Input -->
                        <div class="input-group" :class="{ 'has-error': form.errors.password, 'is-focused': form.password }">
                            <label for="password">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <div class="input-wrapper">
                                <input
                                    id="password"
                                    ref="passwordInput"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Enter your password"
                                    :class="{ 'shake-anim': form.errors.password }"
                                />
                                <button type="button" class="toggle-password" @click="showPassword = !showPassword">
                                    <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                </button>
                            </div>
                            <span v-if="form.errors.password" class="error-text">
                                <i class="fas fa-exclamation-circle"></i> {{ form.errors.password }}
                            </span>
                        </div>

                        <!-- Options Row (Remember me, NO FORGOT PASSWORD per user request) -->
                        <div class="options-row">
                            <label class="custom-checkbox">
                                <input type="checkbox" v-model="form.remember" />
                                <span class="checkmark"></span>
                                <span class="label-text">Remember for 30 days</span>
                            </label>
                            <!-- Space for forgot password intentionally left blank -->
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn-submit" :disabled="form.processing">
                            <span class="btn-text" v-if="!form.processing">Log in</span>
                            <span class="btn-spinner" v-else>
                                <i class="fas fa-circle-notch fa-spin"></i> Authenticating...
                            </span>
                            <div class="ripple-container"></div>
                        </button>

                    </form>
                    
                </div>
            </div>

            <!-- Right Side: Cinematic Visual Panel -->
            <div class="visual-panel">
                <div class="glass-overlay"></div>
                <div class="animated-mesh"></div>
                
                <div class="visual-content">
                    <div class="floating-card metric-card delay-1">
                        <div class="icon-circle badge-green"><i class="fas fa-shield-check"></i></div>
                        <div class="card-text">
                            <h4>Secure Login</h4>
                            <p>End-to-end encrypted</p>
                        </div>
                    </div>
                    
                    <div class="floating-card feature-card delay-2">
                        <div class="icon-circle badge-blue"><i class="fas fa-bolt"></i></div>
                        <div class="card-text">
                            <h4>Lightning Fast</h4>
                            <p>Powered by Inertia.js</p>
                        </div>
                    </div>

                    <div class="hero-text">
                        <h2>Next Generation <br/>Education OS</h2>
                        <p>Manage your classes, coordinate schedules, and track progress effortlessly with MyClass2026.</p>
                    </div>
                </div>
                
                <div class="particles-layer">
                    <div class="particle" v-for="i in 15" :key="i" :style="`--x: ${Math.random() * 100}%; --y: ${Math.random() * 100}%; --delay: ${Math.random() * 5}s;`"></div>
                </div>
            </div>
            
        </div>
    </div>
</template>

<style scoped>
/* =========================================================
 * Login v1.2 — High-Contrast Readable Design
 * All values hardcoded (scoped style can't set :root vars)
 * ========================================================= */

/* Base page shell */
.login-v12-page {
    width: 100vw;
    height: 100vh;
    margin: 0;
    padding: 0;
    background-color: #f8fafc;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
}

/* Split Layout */
.split-container {
    display: flex;
    width: 100%;
    height: 100%;
    background: #ffffff;
    position: relative;
}

/* ========================
   LEFT SIDE: FORM PANEL
   ======================== */
.form-panel {
    flex: 0 0 45%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 2rem 4rem;
    background: #ffffff;
    position: relative;
    z-index: 10;
    box-shadow: 4px 0 30px rgba(0,0,0,0.06);
}

.form-content {
    width: 100%;
    max-width: 440px;
}

/* Brand Header */
.brand-header {
    margin-bottom: 2rem;
}

.logo-box {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #0ea5e9, #3b82f6);
    border-radius: 16px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #ffffff;
    font-size: 24px;
    margin-bottom: 1.25rem;
    box-shadow: 0 8px 20px rgba(14, 165, 233, 0.35);
}

.brand-name {
    font-size: 30px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.5px;
    margin-bottom: 6px;
    line-height: 1;
}

.brand-name .highlight {
    color: #f59e0b;
}

.welcome-text {
    font-size: 15px;
    color: #475569;
    margin-top: 4px;
}

.status-message {
    background: #ecfdf5;
    color: #059669;
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 20px;
    border: 1px solid #a7f3d0;
}

/* Form */
.premium-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.input-group {
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.input-group label {
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 7px;
    letter-spacing: 0.2px;
    transition: color 0.25s;
}

.input-group label i {
    color: #94a3b8;
    font-size: 14px;
    transition: color 0.25s;
}

.input-group:focus-within label {
    color: #0ea5e9;
}

.input-group:focus-within label i {
    color: #0ea5e9;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-wrapper input {
    width: 100%;
    padding: 13px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 15px;
    color: #0f172a;
    background: #f8fafc;
    transition: all 0.25s ease;
    outline: none;
    font-family: inherit;
}

.input-wrapper input::placeholder {
    color: #94a3b8;
    font-size: 14px;
}

.input-wrapper input:focus {
    border-color: #0ea5e9;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.12);
}

/* Password toggle */
.toggle-password {
    position: absolute;
    right: 14px;
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 4px;
    font-size: 16px;
    transition: color 0.2s;
    display: flex;
    align-items: center;
}

.toggle-password:hover {
    color: #475569;
}

/* Options Row */
.options-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Custom Checkbox */
.custom-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-size: 14px;
    color: #475569;
    font-weight: 500;
    user-select: none;
}

.custom-checkbox input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.checkmark {
    height: 20px;
    width: 20px;
    min-width: 20px;
    background-color: #f1f5f9;
    border: 2px solid #cbd5e1;
    border-radius: 6px;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.custom-checkbox input:checked ~ .checkmark {
    background-color: #0ea5e9;
    border-color: #0ea5e9;
}

.custom-checkbox input:checked ~ .checkmark::after {
    content: "";
    width: 5px;
    height: 9px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
    margin-bottom: 1px;
}

/* Submit Button */
.btn-submit {
    position: relative;
    width: 100%;
    padding: 15px;
    margin-top: 0.5rem;
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
    color: #ffffff;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 0.4px;
    cursor: pointer;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 4px 15px rgba(15, 23, 42, 0.25);
    font-family: inherit;
}

.btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(15, 23, 42, 0.35);
    background: linear-gradient(135deg, #1e293b 0%, #1e4976 100%);
}

.btn-submit:active:not(:disabled) {
    transform: translateY(0px);
}

.btn-submit:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

/* Error States */
.has-error .input-wrapper input {
    border-color: #ef4444 !important;
    background: #fef2f2 !important;
}

.has-error label,
.has-error label i {
    color: #ef4444 !important;
}

.error-text {
    font-size: 13px;
    font-weight: 500;
    color: #ef4444;
    display: flex;
    align-items: center;
    gap: 5px;
}

.shake-anim {
    animation: shake 0.45s cubic-bezier(.36,.07,.19,.97) both;
}

@keyframes shake {
    10%, 90% { transform: translate3d(-2px, 0, 0); }
    20%, 80% { transform: translate3d(3px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-5px, 0, 0); }
    40%, 60% { transform: translate3d(5px, 0, 0); }
}

/* ========================
   RIGHT SIDE: VISUAL PANEL
   ======================== */
.visual-panel {
    flex: 0 0 55%;
    position: relative;
    background: #0f172a;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 4rem;
}

/* Animated gradient mesh */
.animated-mesh {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background-image:
        radial-gradient(circle at 20% 55%, rgba(14, 165, 233, 0.45) 0%, transparent 55%),
        radial-gradient(circle at 80% 20%, rgba(245, 158, 11, 0.35) 0%, transparent 55%),
        radial-gradient(circle at 50% 90%, rgba(99, 102, 241, 0.3) 0%, transparent 50%);
    filter: blur(50px);
    animation: meshPulse 12s ease-in-out infinite alternate;
}

@keyframes meshPulse {
    0%   { transform: scale(1)    translate(0,   0);   }
    50%  { transform: scale(1.05) translate(-1%, 2%); }
    100% { transform: scale(1.1)  translate(2%, -2%); }
}

/* Visual content wrapper */
.visual-content {
    position: relative;
    z-index: 5;
    width: 100%;
    max-width: 560px;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Floating glassmorphism cards */
.floating-card {
    background: rgba(255, 255, 255, 0.10);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 16px;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    color: #ffffff;
    width: fit-content;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.25);
    position: absolute;
    animation: floatCard 6s ease-in-out infinite;
}

.metric-card { top: 18%; right: 6%; }
.feature-card { top: 56%; right: -2%; }
.delay-1 { animation-delay: 0s; }
.delay-2 { animation-delay: 3s; }

@keyframes floatCard {
    0%   { transform: translateY(0px); }
    50%  { transform: translateY(-14px); }
    100% { transform: translateY(0px); }
}

.icon-circle {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.badge-green { background: rgba(16, 185, 129, 0.25); color: #34d399; }
.badge-blue  { background: rgba(96, 165, 250, 0.25); color: #60a5fa; }

.card-text h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: #f1f5f9;
}

.card-text p {
    margin: 3px 0 0;
    font-size: 12px;
    color: #94a3b8;
}

/* Hero headline */
.hero-text {
    padding-right: 18%;
}

.hero-text h2 {
    font-size: 52px;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 20px;
    color: #ffffff;
    /* Subtle gradient: bright white → light blue-grey */
    background: linear-gradient(140deg, #ffffff 0%, #94a3b8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-text p {
    font-size: 18px;
    color: #94a3b8;
    line-height: 1.65;
    max-width: 420px;
}

/* Particles */
.particles-layer {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 2;
}

.particle {
    position: absolute;
    width: 3px;
    height: 3px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 50%;
    top: var(--y);
    left: var(--x);
    animation: floatUp 9s infinite linear var(--delay);
    opacity: 0;
}

@keyframes floatUp {
    0%   { transform: translateY(40px);  opacity: 0; }
    20%  { opacity: 0.8; }
    80%  { opacity: 0.8; }
    100% { transform: translateY(-120px); opacity: 0; }
}

/* ========================
   RESPONSIVE
   ======================== */
@media (max-width: 1024px) {
    .split-container {
        flex-direction: column-reverse;
    }
    .form-panel {
        flex: 1;
        padding: 3rem 2rem;
        border-radius: 28px 28px 0 0;
        margin-top: -28px;
    }
    .visual-panel {
        flex: 0 0 38%;
        padding: 2.5rem 2rem;
    }
    .hero-text { padding-right: 0; }
    .hero-text h2 { font-size: 34px; }
    .hero-text p  { font-size: 16px; }
    .floating-card { display: none; }
}

@media (max-width: 480px) {
    .form-panel {
        padding: 2rem 1.5rem;
    }
    .brand-name { font-size: 26px; }
    .visual-panel { flex: 0 0 28%; }
    .hero-text { text-align: center; }
}
</style>
