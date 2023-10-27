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

## API endpoints
| endpoint | description |
| -------- | -------- |
| GET /api/sync | Syncronize Forum Links. Call this one regularly |
| GET /api/status | Get Yazoo status |
| GET /api/search/{query} | Search links |
| GET /api/link/{id} | Get link data |
| GET /api/crawl/youtube | Crawl Youtube using API |
| GET /api/crawl/images | Crawl images |
| GET /api/crawl/audio | guess what |



## n8n

Use n8n for your api calls.