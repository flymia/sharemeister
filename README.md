# 🚀 Sharemeister

**Sharemeister** is a private, high-performance screenshot hosting instance. It is designed for users who value privacy and want to keep their data on their own infrastructure rather than third-party clouds.

## 🛠️ Features

* **Storage Quotas:** Built-in quota system with visual progress bars (150MB default for users, Infinite for Admins).
* **UI:** Clean, modern dashboard using Bootstrap 5 with high-fidelity UI components.
* **SysAdmin CLI Suite:**
    * `sharemeister:install` - Guided installation and admin enrollment.
    * `sharemeister:import` - Bulk import local directories into a user's account.
    * `sharemeister:clear-user-storage` - Maintenance command to wipe user data via email.
    * `sharemeister:user` - Manage users.
* **Telemetry API:** `/api/health` endpoint for monitoring instance status and disk health.
* **Fortify Integration:** Robust authentication flow including password resets and email verification.

## 📦 Deployment & Installation

### 1. Requirements
* PHP 8.2+
* MariaDB / MySQL / Postgres / SQLite
* Composer
* A Linux host (Ubuntu/Debian recommended) or a Container environment.

### 2. Guided Installation

#### Docker / Podman

We ship a ready to use Docker image, which you can use to deploy the app.

You can use this Docker compose file to deploy the app with it's DB on one go:
```yaml
services:
    app:
        image: ghcr.io/flymia/sharemeister:latest
        container_name: sharemeister-prod-app
        restart: 'always'
        depends_on:
          - db
        links:
          - db
        networks:
          - "sharemeister-prod"
        ports:
          - "127.0.0.1:8006:80"
        volumes:
          - "./sm-data/storage:/var/www/html/storage:z"
          - "./.env:/var/www/html/.env:z"
    db:
        image: docker.io/mariadb:lts
        container_name: sharemeister-prod-db
        networks:
            -  "sharemeister-prod"
        ports:
            - "3306"
        volumes:
            - "./sm-db:/var/lib/mysql:z"
        
        environment:
            MYSQL_ROOT_PASSWORD: <INSERT ROOT PW>
            MYSQL_USER: <INSERT DB USER>
            MYSQL_PASSWORD: <INSERT USER PASSWORD>
            MYSQL_DATABASE: <INSERT DB NAME>

networks:
    sharemeister-prod:
        name: sharemeister-prod
        external: true
```

This will startup the Sharemeister web app on Port 8006 (`localhost`). You can use a reverse Proxy (e.g. nginx or httpd) to expose it to the public.

After the initial bootup it is required to start the Sharemeister installation process to create the admin user. You can go into the console and type the following command to make that happen:

```
[root@localhost ~]% docker exec -it sharemeister-prod-app sh

/var/www/html # php artisan key:generate

/var/www/html # php artisan sharemeister:install
```

#### Bare Metal
The easiest way to set up your instance is via the terminal. This instance is protected by a **Setup Guard**; the web interface will not be accessible until an administrator is created via the CLI.

```bash
# Clone the repository
git clone [https://github.com/flymia/sharemeister.git](https://github.com/flymia/sharemeister.git)
cd sharemeister

# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run the setup
php artisan sharemeister:install
```

The `sharemeister:install` command will guide you through naming your instance, creating the primary Admin account, and setting default storage limits.

## 🏗️ Development Environment
We provide a containerized setup for rapid development.

1. Build Image: `cd Docker && docker build . -t sharemeister-app:dev`
2. Network: `docker network create sharemeister`
3. Environment: Copy .env.example to .env in the root.
4. Spin Up Services: `cd Docker && docker-compose up -d (Database, MailHog, phpMyAdmin)`.
5. Run the workspace: 
```bash
docker run -it --rm --network sharemeister \
  -u $(id -u):$(id -g) \
  -v $(pwd):/app -p 8000:8000 \
  sharemeister-app:dev bash
```
6. Initialize: `php artisan migrate --seed`
7. Install: `php sharemeister:install`

## 📡 API & Metrics

Sharemeister provides a health endpoint for monitoring tools (Grafana/Prometheus/Zabbix):

`GET /api/health`

```json
{
  "instance_name": "Sharemeister Homelab",
  "status": "ok",
  "metrics": {
    "total_storage_used_mb": 1240.5,
    "disk_free_space_gb": 45.2
  },
  "health": {
    "database": "healthy",
    "storage_writable": true
  }
}
```

🤝 Contributing

Created with ❤️ for the self-hosting community.
