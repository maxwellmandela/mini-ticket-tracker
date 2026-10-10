<?php

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Router.php';
require_once __DIR__ . '/../src/controllers/TicketController.php';

header('Content-Type: application/json');

$database = new Database(
    __DIR__ . '/../database/app.sqlite'
);

$db = $database->connection();
$router = new Router();

$items_controller = new TicketController($db);

$router->get('/', function () {
    echo json_encode([
        'message' => 'PHP Ticket Tracker API'
    ]);
});

$router->get('/api/tickets', [$items_controller, 'get']);

$router->post('/api/tickets', [$items_controller, 'create']);

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);