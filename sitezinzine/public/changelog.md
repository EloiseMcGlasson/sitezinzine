# Changelog

## [1.0.18] - 2025-07-07
### Ajout
- Ajout de titre de pages coté admin.
### Correction
- Correction du css de la page Mentions légales.
- Correction de la page home/show.html.twig et du controller EmissionShowController.php pour coller à la vision de Nick. Transfert de la catégorie dans le haut de la page pour laisser la place à la liste des émissions ayant un lien, sur le même thème, du plus récent au plus ancien avec le css associé et le responsive.
- Correction du menu de navigation coté auditeurs qui ne mettait plus en avant le menu dans lequel on était (page active, menu en rouge).
- Correction alignement de la colonne 2 des annonces (à revoir ne prend pas toute la place disponible).
- Correction des block title coté admin.


## [1.0.17] - 2025-07-05
### Ajout
- Ajout du changelog en dynamique sur le tableau de bord du coté admin.
### Correction
- Factorisation de tinymce dans admin.html.twig.
- Correction centrage menu déroulant navbar admin + problème de conflit css avec pagination.
- Correction du rappel des données de catégorie et thème dans le formulaire émission create et edit.

## [1.0.16] - 2025-07-04
### Correction
- Correction du conflit entre turbo (fixation du lecteur en header) et la page recherche (suite et fin !).

## [1.0.15] - 2025-07-04
### Correction
- Correction du conflit entre turbo (fixation du lecteur en header) et la page recherche.

## [1.0.14] - 2025-07-04
### Correction
- Correction du conflit entre turbo (fixation du lecteur en header) et les pages login et registration.