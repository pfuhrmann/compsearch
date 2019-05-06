<?php

namespace Room\Search;

use Elasticsearch\Client as ElasticClient;
use Elasticsearch\ClientBuilder;
use PDO;
use Room\Search\Controllers\ApiController;
use Slim\App as SlimApp;
use Slim\Container;

class SearchApp extends SlimApp
{
    /**
     * Custom constructor to bootstrap container
     */
    public function __construct()
    {
        $c[ElasticClient::class] = function () {
            return ClientBuilder::create()
                ->setHosts(['elastic'])
                ->build();
        };

        $c[ApiController::class] = function (Container $c) {
            return new ApiController($c->get(ElasticClient::class));
        };

        $c[PDO::class] = function () {
            $driver = 'mysql';
            $host = 'mysql';
            $db = 'compsearch';
            $charset = 'utf8mb4';
            $user = 'root';
            $dsn = getenv('DB_DSN') ?: "$driver:host=$host;dbname=$db;charset=$charset";

            return new PDO($dsn, $user, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        };

        parent::__construct($c);
    }
}
