import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// CSRF token ayarı - admin-layout.js sadece gerektiğinde yeniden senkronize edebilir
const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
}
