<?php

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Router.php';
require_once __DIR__ . '/../src/controllers/TicketController.php';

header('Content-Type: application/json');

$host = getenv('DB_HOST') ?: 'db';
$port = getenv('DB_PORT') ?: '5432';
$database = getenv('DB_NAME') ?: 'ticket_tracker';
$username = getenv('DB_USER') ?: 'ticket_app';
$password = getenv('DB_PASSWORD');

if ($password === false || $password === '') {
    http_response_code(500);
    echo json_encode(['error' => 'Database password is not configured']);
    exit;
}

$databaseConnection = new Database(
    $host,
    $port,
    $database,
    $username,
    $password
);

$db = $databaseConnection->connection();

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