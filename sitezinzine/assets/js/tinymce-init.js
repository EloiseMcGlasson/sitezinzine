document.addEventListener("DOMContentLoaded", function () {
    tinymce.init({
        selector: "textarea",  // Applique l'éditeur à tous les éléments <textarea>
        plugins: ['lists', 'link', 'preview'],
        menubar: false,
        statusbar: false,
        toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | outdent indent | bullist numlist | link"
    });

    // Sauvegarde automatiquement les modifications
    tinymce.triggerSave(true, true);
});
