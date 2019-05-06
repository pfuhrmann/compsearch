<?php declare(strict_types=1);

use Room\Search\SearchApp;
use Room\Search\Controllers\ApiController;

require '../vendor/autoload.php';

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

$app = new SearchApp();
$app->get('/', ApiController::class . ':index');
$app->post('/companies', ApiController::class . ':companies');
$app->run();
