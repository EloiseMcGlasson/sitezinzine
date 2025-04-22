# 📘 Site Radio Zinzine — Guide d'installation & Déploiement

🔗 [Français](#-site-radio-zinzine--guide-dinstallation--déploiement) | [English](#-radio-zinzine-site-—-installation--deployment-guide) | [Deutsch](#-radio-zinzine-seite-—-installations--bereitstellungsanleitung)

## 🎹 Présentation
Bienvenue sur le site de **Radio Zinzine**, une radio libre, militante et autogérée.

Ce site permet :
- De présenter les **émissions** de la radio
- De partager les **annonces** et **événements**
- De gérer la **programmation** via [LibreTime](https://libretime.org)
- D'offrir un **espace d'administration** pour l'équipe

---

## ⚙️ Stack technique
- PHP 8.3
- Symfony 7
- Twig (frontend)
- Doctrine ORM (MySQL)
- PHPUnit (tests)
- Docker + Docker Compose
- LibreTime (intégré ou interfacé)

---

## 🧰 Prérequis système
Sur votre machine ou serveur distant :
```bash
sudo apt update && sudo apt install docker.io docker-compose -y
sudo systemctl enable docker
sudo usermod -aG docker $USER
```
> ⚠️ Vous devrez vous déconnecter/reconnecter pour activer le groupe `docker`.

---

## 🐳 Déploiement via Docker (Images GHCR)

### 1. Connexion au registre GitHub Container Registry (GHCR)
```bash
echo $GHCR_TOKEN | docker login ghcr.io -u eloisemcglasson --password-stdin
```

### 2. Récupération des images Docker
```bash
docker pull ghcr.io/eloisemcglasson/sitezinzine-app:latest
docker pull ghcr.io/eloisemcglasson/symfony_db:latest
docker pull ghcr.io/eloisemcglasson/phpmyadmin:latest
```

### 3. Créer un fichier `docker-compose.yml`
```yaml
version: '3.8'

services:
  app:
    image: ghcr.io/eloisemcglasson/sitezinzine-app:latest
    container_name: symfony_app
    ports:
      - "8080:80"
    depends_on:
      - db
    networks:
      - zinzine_net
    environment:
      DATABASE_URL: mysql://user:password@db:3306/symfony

  db:
    image: ghcr.io/eloisemcglasson/symfony_db:latest
    container_name: symfony_db
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: symfony
      MYSQL_USER: user
      MYSQL_PASSWORD: password
    ports:
      - "3306:3306"
    volumes:
      - db_data:/var/lib/mysql
    networks:
      - zinzine_net

  phpmyadmin:
    image: ghcr.io/eloisemcglasson/phpmyadmin:latest
    container_name: symfony_phpmyadmin
    restart: always
    ports:
      - "8081:80"
    environment:
      PMA_HOST: db
      UPLOAD_LIMIT: 100M
      MYSQL_ROOT_PASSWORD: root
    depends_on:
      - db
    networks:
      - zinzine_net

volumes:
  db_data:

networks:
  zinzine_net:
    driver: bridge
```

### 4. Lancer les conteneurs
```bash
docker-compose up -d
```

---

## 🥪 Installer & Préparer le site
```bash
docker-compose exec php composer install
docker-compose exec php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec php bin/console doctrine:fixtures:load --no-interaction
```

---

## 🌐 Accès au site
- Site web Symfony : [http://localhost:8080](http://localhost:8080) ou `http://<IP_SERVEUR>:8080`
- PhpMyAdmin : [http://localhost:8081](http://localhost:8081) ou `http://<IP_SERVEUR>:8081`

---

## 🔐 Accès admin
Des utilisateurs de test sont définis dans `src/DataFixtures/`
> ⚠️ Vérifier que les identifiants sont valides en fonction du contexte (dev/prod)

---

## 🤪 Tests automatisés (PHPUnit)
```bash
docker-compose exec php ./vendor/bin/phpunit
```
> Les tests se trouvent dans `tests/Controller/`

---

## 🌍 HTTPS et nom de domaine (optionnel)
Pour le passage en production :
1. Associez votre nom de domaine (ex : `zinzine.fr`) à l'IP du serveur
2. Ajoutez `nginx-proxy` + `acme-companion`
3. Configurez chaque service avec :
```yaml
environment:
  VIRTUAL_HOST: zinzine.fr
  LETSENCRYPT_HOST: zinzine.fr
  LETSENCRYPT_EMAIL: contact@zinzine.fr
```
📌 Référence : https://github.com/nginx-proxy/acme-companion

---

⚠️ Ne jamais exécuter docker compose down -v sauf si vous êtes prêt à perdre la base de données.

## 🙌 À propos
Ce projet est développé pour **Radio Zinzine**, une radio libre, autogérée et engagée dans la promotion du logiciel libre.

Pour plus d'informations : [https://www.zinzine.domaine](https://www.zinzine.domaine)

Besoin d’aide ? Une documentation plus technique est disponible pour les développeurs.

---

# 📜 Radio Zinzine Site — Installation & Deployment Guide

## 🎹 Overview
Welcome to the **Radio Zinzine** website, a free, grassroots and self-managed radio station.

This site allows you to:
- Present **radio shows**
- Share **announcements** and **events**
- Manage the **schedule** using [LibreTime](https://libretime.org)
- Provide an **admin dashboard** for the team

## ⚙️ Tech Stack
- PHP 8.3
- Symfony 7
- Twig (frontend)
- Doctrine ORM (MySQL)
- PHPUnit (tests)
- Docker + Docker Compose
- LibreTime integration

## 🧰 Requirements
Install Docker and Docker Compose:
```bash
sudo apt update && sudo apt install docker.io docker-compose -y
sudo systemctl enable docker
sudo usermod -aG docker $USER
```

## 🐳 Deployment (GHCR images)
```bash
echo $GHCR_TOKEN | docker login ghcr.io -u eloisemcglasson --password-stdin
```
Then pull the Docker images:
```bash
docker pull ghcr.io/eloisemcglasson/sitezinzine-app:latest
docker pull ghcr.io/eloisemcglasson/symfony_db:latest
docker pull ghcr.io/eloisemcglasson/phpmyadmin:latest
```

Start containers with `docker-compose up -d`, then:
```bash
docker-compose exec php composer install
docker-compose exec php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec php bin/console doctrine:fixtures:load --no-interaction
```

### Access:
- Site: `http://<SERVER_IP>:8080`
- PhpMyAdmin: `http://<SERVER_IP>:8081`

### Tests:
```bash
docker-compose exec php ./vendor/bin/phpunit
```

---

# 📃 Radio Zinzine Seite — Installations- & Bereitstellungsanleitung

## 🎹 Einführung
Willkommen auf der Website von **Radio Zinzine**, einem freien und selbstverwalteten Radiosender.

Diese Seite ermöglicht:
- Präsentation der **Sendungen**
- Veröffentlichung von **Ankündigungen** und **Veranstaltungen**
- Verwaltung des **Sendeplans** mit [LibreTime](https://libretime.org)
- Bereitstellung eines **Adminbereichs** für das Team

## ⚙️ Technologiestack
- PHP 8.3
- Symfony 7
- Twig
- Doctrine ORM (MySQL)
- PHPUnit
- Docker + Docker Compose
- LibreTime Integration

## 🧰 Voraussetzungen
Docker & Docker Compose installieren:
```bash
sudo apt update && sudo apt install docker.io docker-compose -y
sudo systemctl enable docker
sudo usermod -aG docker $USER
```

## 🐳 Deployment (GHCR Images)
```bash
echo $GHCR_TOKEN | docker login ghcr.io -u eloisemcglasson --password-stdin
```
Dann:
```bash
docker pull ghcr.io/eloisemcglasson/sitezinzine-app:latest
docker pull ghcr.io/eloisemcglasson/symfony_db:latest
docker pull ghcr.io/eloisemcglasson/phpmyadmin:latest
```

Container starten mit:
```bash
docker-compose up -d
```
Dann:
```bash
docker-compose exec php composer install
docker-compose exec php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec php bin/console doctrine:fixtures:load --no-interaction
```

### Zugriff:
- Seite: `http://<SERVER_IP>:8080`
- PhpMyAdmin: `http://<SERVER_IP>:8081`

### Tests:
```bash
docker-compose exec php ./vendor/bin/phpunit
```

---

Fertig! Das Projekt ist bereit zur Nutzung und Weiterentwicklung. 🎉

