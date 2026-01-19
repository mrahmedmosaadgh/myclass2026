export default {
  install: (app, options) => {
    // Add global properties for language switching
    app.config.globalProperties.$switchLanguage = (locale) => {
      const i18n = app.config.globalProperties.$i18n;

      // Store the selected language in localStorage first
      localStorage.setItem('locale', locale);

      // Set document direction based on locale
      document.documentElement.dir = locale === 'ar' ? 'rtl' : 'ltr';
      document.documentElement.lang = locale;

      // Reload the page to ensure all components update with new language
      window.location.reload();
    };

    // Add a global property to check if current locale is RTL
    app.config.globalProperties.$isRtl = () => {
      const i18n = app.config.globalProperties.$i18n;
      let currentLocale;

      if (i18n.global) {
        // vue-i18n v9+
        currentLocale = i18n.global.locale.value;
      } else if (typeof i18n.locale === 'object') {
        currentLocale = i18n.locale.value;
      } else {
        currentLocale = i18n.locale;
      }

      return currentLocale === 'ar';
    };

    // Initialize direction based on current locale
    const i18n = app.config.globalProperties.$i18n;
    let currentLocale;

    if (i18n.global) {
      // vue-i18n v9+
      currentLocale = i18n.global.locale.value;
    } else if (typeof i18n.locale === 'object') {
      currentLocale = i18n.locale.value;
    } else {
      currentLocale = i18n.locale;
    }

    document.documentElement.dir = currentLocale === 'ar' ? 'rtl' : 'ltr';
    document.documentElement.lang = currentLocale;
  }
}
