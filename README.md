# Sharemeister

**Sharemeister** is a private, high-performance screenshot server. It allows you to manage screenshots in an easy way and has support for multiple users. Screenshots can be uploaded and shared via the web and Bash scripts. It can also be used with ShareX. It is designed for users who value privacy and want to keep their data on their own infrastructure rather than third-party clouds.

## Features

* **Screenshot upload:** Upload your screenshots using CLI or the web
* **Storage Quotas:** Built-in quota system with visual progress bars.
* **Metadata & Management:** 
    * Tagging support for better organization.
    * Persistent protection to prevent accidental deletion.
* **UI:** Clean, modern dashboard using Bootstrap 5 with high-fidelity UI components.
* **CLI Suite:**
    * `sharemeister:install` - Guided installation and admin enrollment.
    * `sharemeister:import` - Bulk import local directories into a user's account.
    * `sharemeister:clear-user-storage` - Maintenance command to wipe user data.
    * `sharemeister:user` - Manage users.
* **Telemetry API:** `/api/health` endpoint for monitoring instance status and disk health.
* **Fortify Integration:** Robust authentication flow including password resets and email verification.
* **Documentation:** See our [wiki](https://github.com/flymia/sharemeister/wiki) for documentation on how to setup Sharemeister or how to use the API.

## Roadmap

* Make installation in production environments easier

## Deployment & Installation

### 1. Requirements

* PHP 8.2+
* MariaDB / MySQL / Postgres / SQLite
* Composer

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

# Run the setup (Migrations and storage limits are configured automatically)
php artisan sharemeister:install
```

The `sharemeister:install` command will guide you through naming your instance, creating the primary Admin account, and setting default storage limits. It will also automatically run any pending database migrations.

## Development Environment
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

## API & Metrics

Sharemeister provides a health endpoint for monitoring tools (Grafana/Prometheus/Zabbix):

`GET /api/health`

```json
{
  "instance_name":"Lokale Testinstanz",
  "status":"ok",
  "version":"1.0.9",
  "build":"20260709",
  "timestamp":"2026-07-09T12:41:47+00:00",
  "metrics":{
    "total_users":1,
    "total_screenshots":4,
    "total_storage_used_mb":0.14,
    "average_screenshot_size_kb":35.5
  },
  "health":
    {
      "database":"healthy",
      "storage_writable":true,
      "php_version":"8.3.31",
      "disk_free_space_gb":128.48
    }
}
```

## Contributing + Disclaimer

This project initially started without the use of AI. Since then, the world has changed and I've used AI to generate many parts of this project. This is not because I'm to lazy to learn stuff, but rather cause I want to get something running quickly. I am doing this in my free time and this is not my main hobby nor job. I'm a Sysadmin and not a developer.

Feel free to contribute new features/bug fixes/etc!

## License

Sharemeister is licensed under the **GNU General Public License v3.0**. See [LICENSE](LICENSE) for the full text.
