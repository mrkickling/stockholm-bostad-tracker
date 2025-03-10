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


## Privat användning, hosta subscribers i database

1. Använd docker compose (`docker compose up --build -d`) för att starta php-server, mysql-server

2. Kör `bostad_tracker --url http://localhost:8080/get_subscribers.php?api_key=supersecretapikey`


## Privat användning + skicka epost

1. Använd docker compose (`docker compose up --build -d`) för att starta php-server, mysql-server

2. Fyll i smtp-information i .env.example och byt namn på filen till .env

2. Kör `bostad_tracker --url http://localhost:8080/get_subscribers.php?api_key=supersecretapikey --send-emails` från denna directory


### Disclaimer

Detta är ett hobbyprojekt, så om det slutar funka så kan det vara för att jag råkat dra ut sladden.
Mejla mig isåfall på loxdal@proton.me så är jag tacksam.
