# Sharemeister

Simple screenshot hosting application with user backend implemented in Laravel for use with ShareX or cURL or other clients. It provides a basic webinterface to view your uploaded screenshots.

## Features

* Webinterface with user registration, login and API key
* Upload screenshots via the webinterface and generate a share link
* Upload screenshots via REST-API (WIP)
* Automic ShareX template generation (WIP)
* Import existing screenshots into the DB (WIP)
* Link sharing only with password (WIP)
* Deployment strategy and some sort of installation process (WIP)

## Deployment

TODO

## How it works

The basic idea is the following: A user creates an account on the website and creates an API key. With the API key, the user can upload screenshots to the endpoint. The screenshots are then saved on the server and the server returns a share URL which you can paste in Discord, TeamSpeak, forums, etc.

Every screenshot gets a UUID, which is saved in the database. The database also keeps track of basic information about the screenshot.


## Contributing

Contributions are very welcome on this project, as I don't have the neccessary experience to build full stack websites with Laravel. I'm primarily a sysadmin and not a developer. That's why the code will look janky sometimes.

### Setting up dev environment using containers

* Install Docker / Podman
* Build the sharemeister Dev docker image, which is located under `Docker/Dockerfile` using `$ cd Docker; docker build . -t sharemeister-app:dev`
* Create the docker network, which is needed for internal container communication: `$ docker network create sharemeister`
* In the main folder, copy the `.env.example` to `.env`
* Run the docker-compose.yml which is also located in the Docker folder using `$ cd Docker; docker-compose up -d`. These containers are the database, dev mail server and phpMyAdmin.
* Run php artisan commands using the newly built container: `$ docker run -it --rm --network sharemeister -u $(id -u):$(id -g) -v $(pwd):/app -p 8000:8000 sharemeister-app:dev bash`
* Install the composer components: `$ composer install`
* Run the migrations and seed the db with example data: `$ php artisan migrate && php artisan db:seed`

Notice: When you run a dev container, please use `$ php artisan serve --host="0.0.0.0"` as command.
