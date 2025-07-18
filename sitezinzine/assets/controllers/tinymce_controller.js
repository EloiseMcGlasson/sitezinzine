// controllers/tinymce_controller.js
import { Controller } from "@hotwired/stimulus"
import tinymce from "tinymce";

export default class extends Controller {
  connect() {
    if (tinymce.editors.length > 0) {
      tinymce.remove();
    }

   tinymce.init({
  selector: "textarea",
  plugins: ['lists', 'link', 'preview'],
  toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | outdent indent | bullist numlist | link",
  menubar: false,
  statusbar: true,
  resize: true,
  height: 300,
  setup: function (editor) {
    editor.on('change keyup', function () {
      tinymce.triggerSave(); // Met à jour la valeur dans le <textarea>
    });
  }
});

  }

  disconnect() {
    tinymce.remove();
  }
}
