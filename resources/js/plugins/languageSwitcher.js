export default {
  install: (app, options) => {
    // Add global properties for language switching
    app.config.globalProperties.$switchLanguage = (locale) => {
      const i18n = app.config.globalProperties.$i18n;
      
      // Set the new locale - check if it's a ref or direct property
      if (typeof i18n.locale === 'object' && i18n.locale !== null) {
        // It's a ref object
        i18n.locale.value = locale;
      } else {
        // It's a direct property
        i18n.locale = locale;
      }
      
      // Set document direction based on locale
      document.documentElement.dir = locale === 'ar' ? 'rtl' : 'ltr';
      document.documentElement.lang = locale;
      
      // Store the selected language in localStorage
      localStorage.setItem('locale', locale);
      
      // Dispatch a global event that any component can listen to
      document.dispatchEvent(new CustomEvent('language-changed', { 
        detail: { 
          locale: locale,
          isRtl: locale === 'ar'
        }
      }));
    };
    
    // Add a global property to check if current locale is RTL
    app.config.globalProperties.$isRtl = () => {
      const i18n = app.config.globalProperties.$i18n;
      const currentLocale = typeof i18n.locale === 'object' 
        ? i18n.locale.value 
        : i18n.locale;
      
      return currentLocale === 'ar';
    };
    
    // Initialize direction based on current locale
    const currentLocale = typeof app.config.globalProperties.$i18n.locale === 'object' 
      ? app.config.globalProperties.$i18n.locale.value 
      : app.config.globalProperties.$i18n.locale;
      
    document.documentElement.dir = currentLocale === 'ar' ? 'rtl' : 'ltr';
    document.documentElement.lang = currentLocale;
  }
}

