# 🚀 Déploiement en production

Ce projet Symfony est prêt à être déployé via Docker. Voici les instructions destinées au sysadmin ou à la personne en charge de l’infrastructure.

---

## 🐳 Conteneurs

Ce projet contient :
- Une image PHP 8.3 avec Apache (`Dockerfile`)
- Une configuration de `docker-compose.prod.yml` à adapter selon les besoins de l’environnement cible
- Une configuration Apache personnalisée (`docker/apache/apache.conf`)
- Un script d’entrée (`docker/entrypoint.sh`)

---

## ⚙️ Fichier `.env.prod`

Exemple de variables d’environnement à adapter :

```
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=SomeRandomSecretKey
DATABASE_URL="mysql://user:password@symfony_db:3306/symfony?serverVersion=8.0"
MAILER_DSN=smtp://user:pass@mail:587
```

---

## 🛠️ Build et exécution

```bash
# 1. Copier les fichiers sur le serveur
scp -r . serveur:/var/www/project

# 2. Se connecter au serveur
ssh user@serveur

# 3. Construire et lancer les services
docker-compose -f docker-compose.prod.yml up -d --build
```

---

## 📁 Volumes et persistance

Les volumes doivent être définis pour :
- les fichiers uploadés (`/public/uploads`)
- les logs éventuels
- les données persistantes de MySQL

Vérifiez que ces volumes sont montés de façon persistante en dehors du conteneur Docker.

---

## 🔐 SSL & Sécurité

Le conteneur **ne gère pas directement le HTTPS**. Il est recommandé de placer un proxy inverse (nginx, traefik...) devant ce conteneur pour :
- gérer le certificat SSL (ex: via Let’s Encrypt)
- rediriger les requêtes vers le port 80 du conteneur Apache
- appliquer des headers de sécurité

---

## 🧪 Tests en production

Avant d'exposer le projet :
- Vérifiez que l'URL de base (`APP_URL`) est correcte
- Vérifiez les permissions (`chown` du dossier `/var/www/html`)
- Vérifiez que les assets sont bien générés (ou utilisez Webpack encore)

---

## 📞 Besoin d’aide

Ce projet est maintenu par : **[Éloïse McGlasson / Radio Zinzine]**