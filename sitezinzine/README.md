# sitezinzine

symfony 7
php bin/console doctrine:schema:update --force --complete => en cas de "In MetadataStorageError.php line 13:

  The metadata storage is not up to date, please run the sync-metadata-storage command to fix this issue.  "

php bin/console doctrine:migrations:sync-metadata-storage


install pour |slug
composer require twig/string-extra
composer require twig/extra-bundle
install pour bootstrap
composer require symfony/webpack-encore-bundle
install pour upload fichiers
composer require vich/uploader-bundle

install de la sécurité
php bin/console make:user
php bin/console make:security
bin/console make:security:form-login

install paginator
composer require knplabs/knp-paginator-bundle

pour lancer le mailer:
ouvrir un gitbash dans bin
faire la commande : ./mailpit
ouvrir le navigateur et aller sur l'url http://localhost:8025/

installation de scoop (manager de package et dependance windows => pour l'installation de symfony cli)
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
Invoke-RestMethod -Uri https://get.scoop.sh | Invoke-Expression

installation symfony cli
scoop install symfony-cli
pour lancer le server => symfony server

installation de glide (pour les carrousel)
 npm install @glidejs/glide

 -tinymce pour mise en page auto des forms

 installation format_datetime pour twig
 composer require twig/intl-extra
 composer require twig/extra-bundle
 activer l'extension dans php.ini (décommenter la ligne intl)

 11/09/24 maj de twig de la 3.11 à la 3.14 pour corriger une vulnerabilité du sandbox
 composer update twig/twig

 installation de phpunit
 composer require --dev phpunit/phpunit

 A new Symfony CLI version is available (5.11.0, currently running 5.9.1).

       If you installed the Symfony CLI via a package manager, updates are going to be automatic.
       If not, upgrade by downloading the new version at https://github.com/symfony-cli/symfony-cli/releases
       And replace the current binary (symfony.exe) by the new one.

 [OK] Web server listening
      The Web server is using PHP CGI 8.2.12
      https://127.0.0.1:8000


créer une nouvelle branche
git checkout -b nom_de_ta_branche  # Créer et basculer sur la branche
git push origin nom_de_ta_branche  # Pousser la branche sur le dépôt distant
git branch                         # Vérifier la liste des branches
git checkout main                  # Revenir sur la branche principale




 merge d'une branche avec main
 git checkout main
git pull origin main
git merge nom_de_ta_branche
# Résoudre les conflits si nécessaire
git commit # Si des conflits ont été résolus
git push origin main

supprimer une branche 
git checkout main                        # Assure-toi d'être sur une autre branche
git branch -d nom_de_ta_branche          # Supprime la branche localement
# ou
git branch -D nom_de_ta_branche          # Supprime la branche localement (force)
git push origin --delete nom_de_ta_branche # Supprime la branche sur le dépôt distant


installation de turbo symfony ux pour que le lecteur audio ne soit pas coupé lors de la navigation


composer require symfony/ux-turbo
