Search
===============
Demo API interfacing with elastic search written in Slim PHP microframework

## Main Components
- Console interface (`symfony/console`)
- HTTP API interface (`slim/slim`)

## Requirements
* `docker`
* `docker-compose`

Run API server
-----------
```bash
docker-compose up -d
```

Installing Dependencies
-----------
```bash
docker-compose run --rm app composer install
```

Import data
-----------
```bash
docker-compose run --rm app console db:init
```
```bash
docker-compose run --rm app console data:import
```

Testing
-------
Unit tests are provided in the `/tests` folder.

To execute tests, run following:
```bash
docker-compose run --rm app bin/phpunit
```

Example HTTP API requests
-------
* Search by name and website
```bash
curl -X POST \
  http://localhost:8888/companies \
  -H 'Content-Type: application/json' \
  -d '{
  "query" : {
    "bool" : {
      "should": [{
          "match": {
              "name": "libranda"
          }
      }, {
          "match": {
              "website": "www.gescola.com"
          }
      }]
    }
  }
}'
```

* Search all companies in food industry without delivery tag
```bash
curl -X POST \
  http://localhost:8888/companies \
  -H 'Content-Type: application/json' \
  -d '{
  "query" : {
    "bool" : {
      "must_not" : {
        "match": {
          "tags": "delivery"
        }
      },
      "must": {
        "match": {
           "industry": "food"
        }
      }
    }
  }
}'
```

* Search by city name
```bash
curl -X POST \
  http://localhost:8888/companies \
  -H 'Content-Type: application/json' \
  -d '{
  "query" : {
    "bool" : {
      "should": [{
          "match": {
              "hq": "Barcelona"
          }
      }, {
          "match": {
              "hq": "Juncosa"
          }
      }]
    }
  }
}'
```
