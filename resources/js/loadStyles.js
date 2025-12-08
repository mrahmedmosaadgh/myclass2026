// Lazy load heavy CSS files
import('../css/app.css');
import('../css/rtl.css');
import('../css/app-layout.css');
import('katex/dist/katex.min.css');
import('vue3-toastify/dist/index.css');
import('quasar/dist/quasar.css');
import('@quasar/extras/material-icons/material-icons.css');

if (localStorage.getItem('offlineMode') === 'true') {
    import('nprogress/nprogress.css');
}