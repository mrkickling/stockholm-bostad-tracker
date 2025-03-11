# stockholm-bostad-tracker
Håll koll på bostadskön utan att aktivt gå in varje dag

## Live version

Skriv upp dig för att få epost med nya lägenheter som matchar ditt filter på
https://joakimloxdal.se/projekt/stockholm-bostad-tracker


## Installation

`pip install .` i denna directory.


## Privat användning med fil

1. Skapa en fil med subscribers (json, se exempel i ./example_configs)

2. Kör `bostad_scraper --file <subscriber file>`


## Privat användning med url / databas

1. Använd docker compose (`docker compose up --build -d`) för att starta php-server, mysql-server

2. Döp om .env.example i ./php_backend till .env

3. Gå in på http://localhost:8080 och registrera användare

4. Kör `bostad_tracker --url http://localhost:8080/subscribers.php?api_key=supersecretapikey`


## Privat användning + skicka epost

1. Följ instruktioner för privat användning med fil eller url

2. Skapa .env med smtp-information baserat på .env.example i denna directory

3. Kör `bostad_tracker [--url <url>] [--file <fil>] --send-emails` från denna directory
