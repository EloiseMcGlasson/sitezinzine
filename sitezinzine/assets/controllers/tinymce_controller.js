import { Controller } from "@hotwired/stimulus";
import tinymce from "tinymce";

// Thème & icônes
import 'tinymce/themes/silver';
import 'tinymce/icons/default';

// Plugins
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/link';
import 'tinymce/plugins/image';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/anchor';
import 'tinymce/plugins/pagebreak';
import 'tinymce/plugins/searchreplace';
import 'tinymce/plugins/wordcount';
import 'tinymce/plugins/visualblocks';
import 'tinymce/plugins/visualchars';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/insertdatetime';
import 'tinymce/plugins/media';
import 'tinymce/plugins/nonbreaking';
import 'tinymce/plugins/save';
import 'tinymce/plugins/table';
import 'tinymce/plugins/directionality';
import 'tinymce/plugins/emoticons';

export default class extends Controller {
  static values = {
  css: String
}

  connect() {
    this.reloadEditor = this.init.bind(this);

    // Attache un événement personnalisé en cas de reload (utile avec Turbo)
    this.element.addEventListener('tinymce:reload', this.reloadEditor);

    // Lance l'init immédiat avec un léger délai pour s'assurer du DOM
    setTimeout(() => this.init(), 0);
  }

  disconnect() {
    const textarea = this.element.querySelector('textarea');
    if (textarea && tinymce.get(textarea.id)) {
      tinymce.remove(tinymce.get(textarea.id));
    }

    this.element.removeEventListener('tinymce:reload', this.reloadEditor);
  }

  init() {
    const textarea = this.element.querySelector('textarea');
    if (!textarea) {
      console.warn("Aucun <textarea> trouvé pour TinyMCE.");
      return;
    }

    // Vérifie que le textarea a un ID
    if (!textarea.id) {
      textarea.id = `tinymce-${Math.random().toString(36).substring(2, 9)}`;
    }

    // Si TinyMCE est déjà initialisé, on le supprime d'abord
    const existing = tinymce.get(textarea.id);
    if (existing) {
      tinymce.remove(existing);
    }

    tinymce.init({
      target: textarea,
      content_css: [
        this.cssValue,
        'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700;900&family=Montserrat+Alternates:wght@400;500;700;900&display=swap'
      ],
      content_style: `
        html { background: linear-gradient(#E4013A, #B81C61); }`,
      body_class: 'page-content',
      language: 'fr_FR',
      language_url: 'https://cdn.tiny.cloud/1/no-api-key/tinymce/6/langs/fr_FR.js',

      plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'pagebreak', 'searchreplace', 'wordcount', 'visualblocks',
        'visualchars', 'code', 'fullscreen', 'insertdatetime', 'media',
        'nonbreaking', 'save', 'table', 'directionality', 'emoticons'
      ],

      toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | " +
        "outdent indent | bullist numlist | link image media | table | code preview fullscreen",
      relative_urls: false,
      remove_script_host: false,
      convert_urls: true,
      document_base_url: window.location.origin + '/',

      menubar: false,
      height: 300,

      skin_url: '/build/skins/ui/oxide',
      base_url: '/build',
      suffix: '.min',
      license_key: 'gpl',

      automatic_uploads: true,
      file_picker_types: 'image',

      // ✅ UPLOAD HANDLER (ICI)
      images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.withCredentials = true; // 🔥 indispensable pour éviter le 302 /login
        xhr.open('POST', '/admin/tinymce/upload');

        xhr.onload = () => {
          if (xhr.status < 200 || xhr.status >= 300) {
            return reject('HTTP Error: ' + xhr.status);
          }

          let json;
          try {
            json = JSON.parse(xhr.responseText);
          } catch (e) {
            return reject('Invalid JSON: ' + xhr.responseText);
          }

          if (!json.location) {
            return reject('Invalid response: missing location');
          }

          resolve(json.location);
        };

        xhr.onerror = () => reject('XHR Transport Error');

        const formData = new FormData();
        formData.append('file', blobInfo.blob(), blobInfo.filename());
        xhr.send(formData);
      }),

      setup: (editor) => {
        editor.on('change keyup', () => {
          editor.save();
        });
      }
    });


  }
}
