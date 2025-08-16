// assets/app.js
import './styles/reset.css';
import './styles/app.css';
import './bootstrap.js';
import 'flatpickr/dist/flatpickr.min.css';

document.addEventListener('turbo:load', () => {
  document.querySelectorAll('[data-controller="tinymce"]').forEach(el => {
    el.dispatchEvent(new Event('tinymce:reload'));
  });
});

console.log('App ready 🎉');
