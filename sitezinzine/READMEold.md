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

 installation pour les annotations doctrine
 composer require doctrine/annotations


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

commande pour envoyer les log de test dans un fichier
docker exec -it symfony_app php bin/phpunit tests/Controller/Admin/EmissionControllerTest.php > tests.log

ajout de git attibutes pour ignorer les fichiers dev lors de merge avec main
commande pour activer gitattributes
git config --global merge.ours.driver true

✅ Étapes PowerShell valides pour sauvegarder la bdd

manuel
# 1. Créer un timestamp pour nommer la sauvegarde
$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"

# 2. Sauvegarder la base dans un fichier SQL
docker exec symfony_db sh -c "exec mysqldump -u root -p'root' symfony" > "backup-$timestamp.sql"

# 3. Compresser le fichier en ZIP
Compress-Archive -Path "backup-$timestamp.sql" -DestinationPath "backup-$timestamp.zip"

# 4. (Facultatif) Supprimer le fichier SQL pour ne garder que le ZIP
Remove-Item "backup-$timestamp.sql"

automatique
# 5. Lancer le script PowerShell pour sauver la bdd
& "C:\Users\mcgla\xampp\htdocs\siteZinzine\backup-db.ps1"



🔁 Restauration de la base de données MySQL (dans Docker) PowerShell

# 1. Décompresser le fichier .zip
Si ton backup est dans un fichier comme backup-20250422-2010.zip, exécute :

Expand-Archive -Path "backup-20250422-2010.zip" -DestinationPath .
Cela extrait un fichier .sql (par exemple backup-20250422-2010.sql) dans le dossier courant.

# 2. Restaurer le dump .sql dans la base
Exécute la commande suivante pour importer dans la base symfony :

docker exec -i symfony_db sh -c "exec mysql -u root -p'root' symfony" < "backup-20250422-2010.sql"
✅ Cela injecte le contenu du fichier .sql directement dans ta base MySQL.

# 3. (Optionnel) Supprimer le fichier .sql après usage
Tu peux le supprimer pour gagner de la place :

Remove-Item "backup-20250422-2010.sql"

automatique
# 4. Lancer le script PowerShell pour sauver la bdd
& "C:\Users\mcgla\xampp\htdocs\siteZinzine\restore-db.ps1"
