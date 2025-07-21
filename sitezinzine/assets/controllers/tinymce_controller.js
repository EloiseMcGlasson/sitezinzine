// controllers/tinymce_controller.js
import { Controller } from "@hotwired/stimulus";
import tinymce from "tinymce";
import 'tinymce/themes/silver'; // Si tu veux utiliser le thème silver (ou tout autre thème)
import 'tinymce/icons/default'; // Si tu veux utiliser les icônes par défaut
// Importation des plugins un par un
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
    // Sélectionne le textarea qui est dans cet élément contrôlé
    const textarea = this.element.querySelector("textarea");

    // Si l'éditeur est déjà initialisé sur cet élément, on le supprime avant d'en initialiser un nouveau
    if (tinymce.get(textarea.id)) {
      tinymce.remove(textarea); // Supprime l'éditeur existant sur ce textarea spécifique
    }

    // Initialisation de TinyMCE sur le textarea sélectionné
   tinymce.init({
  target: textarea,
  language: 'fr_FR',
  language_url: 'https://cdn.tiny.cloud/1/no-api-key/tinymce/6/langs/fr_FR.js',
  plugins: ['lists', 'link', 'preview'],
  toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | outdent indent | bullist numlist | link",
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

  disconnect() {
    // On nettoie l'éditeur lorsqu'il est déconnecté
    const textarea = this.element.querySelector("textarea");
    tinymce.remove(textarea);
  }
}
