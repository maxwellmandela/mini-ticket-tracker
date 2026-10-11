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

    public function create(): void
    {
        $body = json_decode(file_get_contents('php://input'), true);

        if (!is_array($body) || json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON body']);
            return;
        }

        $title = $body['title'] ?? null;
        $description = $body['description'] ?? null;
        $priority = $body['priority'] ?? 'medium';

        if (!is_string($title) || trim($title) === '' || strlen($title) > 255) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Title is required and must not exceed 255 characters'
            ]);
            return;
        }

        if ($description !== null && !is_string($description)) {
            http_response_code(400);
            echo json_encode(['error' => 'Description must be a string or null']);
            return;
        }

        $allowedPriorities = ['low', 'medium', 'high'];

        if (!is_string($priority) ||
            !in_array($priority, $allowedPriorities, true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid priority']);
            return;
        }

        $stmt = $this->database->prepare(
            'INSERT INTO tickets (title, description, priority)
            VALUES (:title, :description, :priority)
            RETURNING id, title, description, status, priority, created_at'
        );

        $stmt->execute([
            ':title' => trim($title),
            ':description' => $description,
            ':priority' => $priority
        ]);

        $ticket = $stmt->fetch();

        http_response_code(201);
        echo json_encode(['item' => $ticket]);
    }

    public function update(int $id): void
    {
        $body = json_decode(file_get_contents('php://input'), true);

        if (!is_array($body) || json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON body']);
            return;
        }

        $allowedStatuses = ['open', 'in_progress', 'closed'];
        $allowedPriorities = ['low', 'medium', 'high'];

        $fields = [];
        $params = [':id' => $id];

        if (array_key_exists('status', $body)) {
            if (!is_string($body['status']) ||
                !in_array($body['status'], $allowedStatuses, true)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid status']);
                return;
            }

            $fields[] = 'status = :status';
            $params[':status'] = $body['status'];
        }

        if (array_key_exists('priority', $body)) {
            if (!is_string($body['priority']) ||
                !in_array($body['priority'], $allowedPriorities, true)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid priority']);
                return;
            }

            $fields[] = 'priority = :priority';
            $params[':priority'] = $body['priority'];
        }

        if (empty($fields)) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Provide status, priority, or both'
            ]);
            return;
        }

        $sql = 'UPDATE tickets SET ' . implode(', ', $fields) .
            ' WHERE id = :id
                RETURNING id, title, description, status, priority, created_at';

        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);

        $ticket = $stmt->fetch();

        if ($ticket === false) {
            http_response_code(404);
            echo json_encode(['error' => 'Ticket not found']);
            return;
        }

        http_response_code(200);
        echo json_encode(['item' => $ticket]);
    }

}