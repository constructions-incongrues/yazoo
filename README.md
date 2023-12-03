# Yazoo

Link Database et Moteur de recherche

## Features
 - Search Images (stray files) .png .jpg .jpeg .gif .webm
 - Search Video (youtube/dailymotion)
 - Search Audio (bandcamp/soundcloud/mp3)
 - Stats
 - Status
 - Blacklisted domains
 - API

## Requirements

- PHP 8.1
- php-curl
- Mysql/MariaDB


## Installation

Configure .env, then:

```
composer install
./bin/console make:migration
./bin/console doctrine:migrations:migrate
```

## API documentation

https://yazoo.constructions-incongrues.net/api/doc

## n8n

Use n8n for your api calls.
