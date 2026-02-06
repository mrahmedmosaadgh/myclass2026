import './bootstrap';
import '../css/app.css';
import '../css/rtl.css'; // Import RTL styles
import '../css/app-layout.css'; // Import layout styles
import 'katex/dist/katex.min.css'; // Import KaTeX styles
import 'vue3-toastify/dist/index.css';
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';

// --- Offline mode toggle ---
const offlineMode = localStorage.getItem('offlineMode') === 'true';

if (offlineMode) {
    // Lazy load offline functionality
    Promise.all([
        import('./offline/dexie.js')
    ]);

    // Lazy register service worker
    if ('serviceWorker' in navigator) {
        setTimeout(() => {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('SW registered:', registration.scope);
                })
                .catch(console.error);
        }, 1000);
    }
}

// Helper for toggling offline mode (call from browser console or UI):
window.setOfflineMode = (enable) => {
    localStorage.setItem('offlineMode', enable ? 'true' : 'false');
    window.location.reload();
};

import { createApp, h } from 'vue';
import { createInertiaApp, router, Head, Link } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { createI18n } from 'vue-i18n';
import Vue3Toastify from 'vue3-toastify';
// Import Quasar components normally (not lazy loaded)
import { Quasar, Notify, Loading, Dialog, Dark, QOptionGroup, QRadio } from 'quasar';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue';
import languageSwitcher from './plugins/languageSwitcher.js';
// Lazy load heavy CSS
import('./loadStyles.js');

// Import language files
import enMessages from './lang/en.json';
import arMessages from './lang/ar.json';

// Import Pinia persistence plugin
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';

// Import our custom page resolver
import { resolvePageComponent as customResolvePageComponent } from './pageResolver.js';

const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

// Configure NProgress
NProgress.configure({
    showSpinner: true,
    minimum: 0.1,
    trickleSpeed: 700,
    easing: 'ease',
    speed: 500
});

const appName = import.meta.env.VITE_APP_NAME || 'myClass';
// Get saved locale from localStorage or default to 'en'
const savedLocale = localStorage.getItem('locale') || 'en';

// Initialize dark mode from localStorage or system preference
const initDarkMode = () => {
    const storedDarkMode = localStorage.getItem('darkMode');

    if (storedDarkMode !== null) {
        // Use stored preference
        Dark.set(storedDarkMode === 'true');
    } else {
        // Use system preference
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        Dark.set(prefersDark);
        localStorage.setItem('darkMode', prefersDark);
    }
};

// Initialize dark mode
initDarkMode();

// Create i18n instance
const i18n = createI18n({
    legacy: false, // Set to false to use Composition API
    locale: savedLocale,
    fallbackLocale: 'en',
    messages: {
        en: enMessages,
        ar: arMessages
    },
    missing: (locale, key) => {
        console.log(`[i18n] Missing translation: ${key} in ${locale} locale`);
        // console.warn(`[i18n] Missing translation: ${key} in ${locale} locale`);
        return key;
    }
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: async (name) => {
        // Try to use our custom resolver for better code splitting
        try {
            // Attempt to use the custom resolver first
            const resolved = await customResolvePageComponent(name);
            
            // Set default layout if none is specified
            if (!resolved.default.layout) {
                resolved.default.layout = AppLayoutDefault;
            }
            return resolved;
        } catch (error) {
            // Fallback to the original resolver if the custom one fails
            const page = resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'));
            return page.then((module) => {
                // Set default layout if none is specified
                if (!module.default.layout) {
                    module.default.layout = AppLayoutDefault;
                }
                return module;
            });
        }
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app
            // .component('LayoutDefualt', AppLayoutDefault)
            .component('InertiaHead', Head)
            .component('Head', Head)
            .component('InertiaLink', Link)
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue)
            .use(i18n)
            .use(languageSwitcher)
            .use(Vue3Toastify, {
                autoClose: 3000,
                position: 'top-right',
            })
            .use(Quasar, {
                plugins: {
                    Notify,
                    Loading,
                    Dialog,
                    Dark // Add Dark plugin for dark mode
                },
                components: {
                    QOptionGroup,
                    QRadio
                },
                config: {
                    notify: {
                        position: 'top-right',
                        timeout: 2500,
                        textColor: 'white'
                    },
                    brand: {
                        primary: '#1976d2',
                        secondary: '#26A69A',
                        accent: '#9C27B0',
                        dark: '#1d1d1d',
                        positive: '#21BA45',
                        negative: '#C10015',
                        info: '#31CCEC',
                        warning: '#F2C037'
                    }
                }
            });

        return app.mount(el);
    },
    progress: false, // Disable Inertia's built-in progress
});

let activeNavigations = 0;

router.on('start', () => {
    activeNavigations++;
    NProgress.start();
});

router.on('finish', () => {
    activeNavigations--;
    if (activeNavigations === 0) {
        NProgress.done();
    }
});

router.on('error', () => {
    activeNavigations--;
    if (activeNavigations === 0) {
        NProgress.done();
    }
});







