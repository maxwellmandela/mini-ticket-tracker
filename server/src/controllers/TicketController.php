<?php

class TicketController {
    private $database;

    public function __construct($db){
        $this->database = $db;
    }

    public function get($id = null) {
        if ($id !== null) {
            $stmt = $this->database->prepare(
                'SELECT id, title, description, status, priority, created_at
                FROM tickets
                WHERE id = :id'
            );

            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $ticket = $stmt->fetch();

            if (!$ticket) {
                http_response_code(404);
                echo json_encode(['error' => 'Ticket not found']);
                return;
            }

            echo json_encode(['item' => $ticket]);
            return;
        }

        $status = $_GET['status'] ?? null;

        $page = filter_var(
            $_GET['page'] ?? 1,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        $limit = filter_var(
            $_GET['limit'] ?? 10,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 100]]
        );

        if ($page === false || $page === null ||
            $limit === false || $limit === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid page or limit']);
            return;
        }

        $allowedStatuses = ['open', 'in_progress', 'closed'];

        if ($status !== null && !in_array($status, $allowedStatuses, true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid status']);
            return;
        }

        $offset = ($page - 1) * $limit;

        $sql = 'SELECT id, title, description, status, priority, created_at
                FROM tickets';

        if ($status !== null) {
            $sql .= ' WHERE status = :status';
        }

        $sql .= ' ORDER BY created_at DESC, id DESC
                LIMIT :limit OFFSET :offset';

        $stmt = $this->database->prepare($sql);

        if ($status !== null) {
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        echo json_encode([
            'items' => $stmt->fetchAll(),
            'page' => $page,
            'limit' => $limit
        ]);

    }

    public function create(){
        $body = json_decode(
            file_get_contents('php://input'),
            true
        );

        echo json_encode([
            'received' => $body
        ]);
    }

    public function update(){
        $body = json_decode(
            file_get_contents('php://input'),
            true
        );

        echo json_encode([
            'received' => $body
        ]);
    }

}