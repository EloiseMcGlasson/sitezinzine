# Changelog

## [1.0.38] - 2025-07-??*
### Ajout
- Ajout de la page admin/evenement/show qui avait été oublié.
### Correction
- Correction css de showevenement.
- Correction du controller tinymce qui n'était pas parfaitement opérationnel ( bug du champ descriptif sur les formulaire ).
- Gestion de la redirection après suppression d'émissions, annonces, évènements ou de catégories.


## [1.0.37] - 2025-07-24
### Correction
- Correction du formulaire d'édition d'émission, si pas d'animateur·ice référent·e indiqué, prend le nom de la personne qui édite l'émission, si le descriptif est vide, met "descriptif à remplir".
- Correction de la redirection des pages après validation de formulaire edit.


## [1.0.36] - 2025-07-24
### Ajout
- Ajout de la recherche côté admin, selectionne aussi les émissions sans fichier audio.
### Correction
- Correction de la logique d'affichage des émissions côté admin, maintenant une émission qui n'a pas de fichier son, mais qui a un user s'affiche(j'espère !).
- Correction de la couleur de la bordure de la catégorie sur la page home/show (du rouge vers le noir).


## [1.0.35] - 2025-07-23
### Ajout
- Ajout du responsive pour le lecteur.
### Correction
- Correction des noms de groupe de thèmes.
- Correction côté admin de l'affichage des émissions n'ayant pas d'url mais ayant un user.
- Déplacement du menu invité ancien·nes animateur·ices dans le groupe admin droit user.


## [1.0.34] - 2025-07-23
### Ajout
- Ajout d'un début de responsive sur la navbar publique (en attente de retour des graphistes).
### Correction
- Correction de la logique d'affichage des émissions côté admin, maintenant une émission qui n'a pas de fichier son, mais qui a un user s'affiche.
- Correction de la navbar publique (espacement avec le bord sur les icone de zinzine et la loupe de recherche).
- Uniformisation des tailles sur les balise h1 et h3 de la page d'accueil, décalage des images à gauche du titre.
- Réduction à 3 lignes des descriptifs dans les listes d'émissions (home/emission, admin/emission, home/show).
- Fin de refonte du home/show selon la maquette de Fernande.


## [1.0.33] - 2025-07-22
### Correction
- Hotfix affichage des émissions côté admin.

## [1.0.32] - 2025-07-22
### Correction
- bug de migration de base de donnée changement des annotations entity diffusion.


## [1.0.30] - 2025-07-22
### Correction
- Correction de la recherche (bug de date obligatoire) et classement par date de diffusion decroissante avec affichage de la date de diffusion.
- Optimisation de certaine requêtes car la table diffusion est très lourde et ça ralentissait beaucoup le site (à vérifer).
- Correction de la requête qui empêchait d'aller dans le menu émission du site publique.
- Début de refonte de la page home/show.html.twig selon le modèle de Fernande (à suivre).
- Modification de la base de donnée entity emission et diffusion pour optimisation.


## [1.0.29] - 2025-07-21
### Ajout
- Ajout de la gestion de l'affichage de categorie.show si elle n'a pas d'émission.
- Ajout de la diffusion : Entity, Controller, Form, Repository, et les templates.
- Ajout de la table et des données de diffusion (migration ).
### Correction
- Correction des templates affectés par l'affichage de la diffusion.
- Correction des formulaires create et edit (url null, si ref vide user_id).
- Correction du formulaire de recherche qui forçait à mettre des dates.


## [1.0.28] - 2025-07-20
### Ajout
- Ajout des alt sur les images de la navbar côté auditeurices.
- Ajout des controllers tinymce et flatpickr.
- Ajout d'une favicon.ico.
### Correction
- Correction du partial vague qui soulignait les liens.
- Correction de l'implémentation de tinymce, conflit stimulus et webpack helper.
- Corrections des fichiers docker en conséquence.


## [1.0.26] - 2025-07-20
### Ajout
- Ajout d'un bouton supprimer pour les émissions de la catégorie archive (visible uniquement pour superadmin).
- Ajout d'une nouvelle règle de nommage des fichiers uploadés afin d'éviter les bugs de charactères spéciaux (vitch).
### Correction
- Correction de la redirection de page quand on validait un changement sur une émission ou une catégorie, maintenant on se retrouve sur la page précédent le formulaire et pas sur la première page de la liste.
- Correction de l'affichage des images en background cartes de lastemission contenant un charactère spécial.
- Factorisation du code de la page categorie.show pour éviter des doublons.
- Correction du css de la page admin.emission.show, le blanc de la partie catégorie ne descendait pas jusqu'en bas.

## [1.0.25] - 2025-07-19
### Base de données
- Récupération de la base de données réelle pour tests de script d'adaptation.
### Correction
- Correction du glide.js avec stimulus pour que le chargement du carrousel soit plus fluide.
- correction du coupage automatique des titre d'émission sur l'affichage de lastemission et carrousel.


## [1.0.24] - 2025-07-18
### Ajout
- Ajout du bouton pause sur le lecteur et le js nécessaire pour que ça fonctionne.
- Ajout d'un lien vers le site publique sur le logo radio zinzine de la navbar côté admin.
- Ajout de Node.js.
### Correction
- Correction des groupes de thèmes (rajout de /).
- Correction de l'édition des évènements qui ne préremplissait pas le champ titre du formulaire.
- Correction des formulaires utilisant tinymce avec un controller stimulus afin d'éviter le rechargement de la page à cause de turbo.
- Correction des filtres dynamiques des thèmes sur la page home/show.


## [1.0.23] - 2025-07-16
### Serveur
- Modification des droits du dossier uploads pour permettre le chargement des images.
### Correction
- Correction des groupes de thèmes selon la nouvelle proposition.


## [1.0.22] - 2025-07-15
### Correction
- Correction du js de la page home/show.html.twig qui avait besoin d'un refresh pour fonctionner (pb de turbo:load)


## [1.0.21] - 2025-07-15
### Ajout
- Ajout de la logique de groupe de thème pour le partial lastemissionbytheme qui devient lastEmissionsByGroupTheme.
- Ajout du js pour le filtrage dynamique par thème dans la page home/show.html.twig.
### Correction
- Correction du formulaire de création de thème (problème d'updateAt et de dateimmutable).
- Correction de la gestion de l'affichage des descriptions d'émission sur la page emission home/show.html.twig qui pouvaient dépasser du cadre et déformer la page.
- Modification de la base de donnée pour que chaque émission n'ai qu'un seul thème.


## [1.0.20] - 2025-07-07
- Ouverture du site en démo aux zinzinien·nes.
### Ajout
- Ajout du contenu provisoire pour la page infos.
- Ajout du widget grille hebdomadaire de libretime dans la partie programme (mais c'est moche ...). 


## [1.0.19] - 2025-07-06
### Ajout
- Ajout du bouton télécharger sur la liste des émission en bas de page home/show.html.twig.
- Ajout du tableau infos diverses, horaire de passage, liens, dans la partie émission de la page home/show.html.twig.
- Ajout d'un contenu provisoire pour les pages aide à l'écoute et zone d'écoute (en attente de Klaus) et contacts (en attente de Joëlle).
### Correction
- Correction de la couleur des H1 et des liens a et a:hover dans la page home/amis.html.twig et modification de la hierarchisation des titres H.
- Correction du css de la page don.
- Correction de la gestion de l'affichage des images sur la page emission home/show.html.twig.
- Correction des bloc title des pages aides à l'écoute, zone d'écoute et contact.


## [1.0.18] - 2025-07-06
### Ajout
- Ajout de titre de pages côté admin.
### Correction
- Correction du css de la page Mentions légales.
- Correction de la page home/show.html.twig et du controller EmissionShowController.php pour coller à la vision de Nick. Transfert de la catégorie dans le haut de la page pour laisser la place à la liste des émissions ayant un lien, sur le même thème, du plus récent au plus ancien avec le css associé et le responsive. En prévision du changement de gestion des thèmes et leurs affichages.
- Correction du menu de navigation côté auditeurs qui ne mettait plus en avant le menu dans lequel on était (page active, menu en rouge).
- Correction alignement de la colonne 2 des annonces (à revoir ne prend pas toute la place disponible).
- Correction des block title côté admin.


## [1.0.17] - 2025-07-05
### Ajout
- Ajout du changelog en dynamique sur le tableau de bord du côté admin.
### Correction
- Factorisation de tinymce dans admin.html.twig.
- Correction centrage menu déroulant navbar admin + problème de conflit css avec pagination.
- Correction du rappel des données de catégorie et thème dans le formulaire émission create et edit.


## [1.0.16] - 2025-07-04
### Correction
- Correction du conflit entre turbo (fixation du lecteur en header) et la page recherche problème de chargement du js(suite et fin !).


## [1.0.15] - 2025-07-04
### Correction
- Correction du conflit entre turbo (fixation du lecteur en header) et la page recherche.


## [1.0.14] - 2025-07-04
### Correction
- Correction du conflit entre turbo (fixation du lecteur en header) et les pages login et registration.