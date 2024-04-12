# Sharemeister

Simple screenshot hosting application with User backend implemented in Laravel for use with ShareX or cURL or other clients.

## Setting up dev environment

### Using containers (Docker, Podman, etc.)

* Install Docker / Podman
* Build the Sharemeister Dev docker image, which is located under `Docker/Dockerfile` using `$ cd Docker; docker build . -t sharemeister-app:dev`
* Create the docker network, which is needed for internal container communication: `$ docker network create sharemeister`
* In the main folder, copy the `.env.example` to `.env`
* Run the docker-compose.yml which is also located in the Docker folder using `$ cd Docker; docker-compose up -d`. These containers are the database and a GUI using phpMyAdmin.
* Run php artisan commands using the newly built container: `$ docker run -it --rm --network sharemeister -u $(id -u):$(id -g) -v $(pwd):/app -p 8000:8000 sharemeister-app:dev bash`
* Install the composer components: `$ composer install`
* Run the migrations and seed the db with example data: `$ php artisan migrate && php artisan db:seed`

Notice: When you run a dev container, please use `$ php artisan serve --host="0.0.0.0"` as command.
