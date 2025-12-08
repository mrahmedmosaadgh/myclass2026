import { computed, onMounted } from 'vue';
import { Dark } from 'quasar';

/**
 * Composable for managing dark mode across the application using Quasar's Dark plugin
 * 
 * @returns {Object} Dark mode utilities
 */
export function useDarkMode() {
    // Use computed property to track Quasar's Dark mode state
    const isDarkMode = computed(() => Dark.isActive);

    /**
     * Toggle dark mode
     */
    const toggleDarkMode = () => {
        // Toggle Quasar's Dark mode
        Dark.toggle();
        
        // Save preference to localStorage
        localStorage.setItem('darkMode', Dark.isActive);
        
        // Ensure the 'dark' class is applied to the HTML element for Tailwind
        document.documentElement.classList.toggle('dark', Dark.isActive);
    };

    /**
     * Initialize dark mode from localStorage or system preference
     */
    const initDarkMode = () => {
        // Check localStorage first
        const storedDarkMode = localStorage.getItem('darkMode');
        
        if (storedDarkMode !== null) {
            // Use stored preference if available
            Dark.set(storedDarkMode === 'true');
        } else {
            // Otherwise check system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            Dark.set(prefersDark);
            localStorage.setItem('darkMode', prefersDark);
        }
        
        // Ensure the 'dark' class is applied to the HTML element for Tailwind
        document.documentElement.classList.toggle('dark', Dark.isActive);
    };

    // Initialize on component mount
    onMounted(() => {
        // Ensure the 'dark' class is applied to the HTML element for Tailwind
        document.documentElement.classList.toggle('dark', Dark.isActive);
        
        // Listen for system preference changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (localStorage.getItem('darkMode') === null) {
                Dark.set(e.matches);
                document.documentElement.classList.toggle('dark', Dark.isActive);
                localStorage.setItem('darkMode', Dark.isActive);
            }
        });
    });

    return {
        isDarkMode,
        toggleDarkMode,
        initDarkMode
    };
}
