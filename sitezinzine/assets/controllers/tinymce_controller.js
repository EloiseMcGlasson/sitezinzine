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
      language: 'fr_FR',
      language_url: 'https://cdn.tiny.cloud/1/no-api-key/tinymce/6/langs/fr_FR.js',
      plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'pagebreak', 'searchreplace', 'wordcount', 'visualblocks',
        'visualchars', 'code', 'fullscreen', 'insertdatetime', 'media',
        'nonbreaking', 'save', 'table', 'directionality', 'emoticons'
      ],
      toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | " +
               "outdent indent | bullist numlist | link",
      menubar: false,
      height: 300,
      skin_url: '/build/skins/ui/oxide',
      content_css: '/build/skins/content/default/content.css',
      base_url: '/build',
      suffix: '.min',
      license_key: 'gpl',
      setup: function (editor) {
        editor.on('change keyup', function () {
          editor.save();
        });
      }
    });
  }
}
