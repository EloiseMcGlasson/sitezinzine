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





