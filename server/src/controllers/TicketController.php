<?php

class TicketController {
    private $database;

    public function __construct($db){
        $this->database = $db;
    }

    public function get(){
        $items = [];

        echo json_encode([
            'items' => $items
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
}