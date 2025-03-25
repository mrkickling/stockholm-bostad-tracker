# stockholm-bostad-tracker
Håll koll på bostadskön utan att aktivt gå in varje dag

## Live version

Skriv upp dig för att få epost med nya lägenheter som matchar ditt filter på
https://joakimloxdal.se/projekt/stockholm-bostad-tracker


## Installation

`pip install .` i denna directory.


## Privat användning

1. Använd docker compose (`docker compose up --build -d`) för att starta php-server, mysql-server

2. Döp om .env.example i ./php_backend till .env

3. Gå in på http://localhost:8080 och registrera användare

4. Kör `bostad_tracker http://localhost:8080 supersecretapikey`
