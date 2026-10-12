<?php

use PHPUnit\Framework\TestCase;

final class TicketApiTest extends TestCase
{
    private string $baseUrl = 'http://localhost:8000';

    public function testCanCreateTicket(): void
    {
        $payload = json_encode([
            'title' => 'PHP integration test ticket',
            'description' => 'Created during API testing',
            'priority' => 'medium',
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'ignore_errors' => true,
            ],
        ]);

        $response = file_get_contents(
            $this->baseUrl . '/api/tickets',
            false,
            $context
        );

        $statusLine = $http_response_header[0] ?? '';

        $this->assertNotFalse($response);
        $this->assertStringContainsString('201', $statusLine);

        $data = json_decode($response, true);

        $this->assertIsArray($data);
        $this->assertSame(
            'PHP integration test ticket',
            $data['item']['title']
        );
        $this->assertArrayHasKey('id', $data['item']);
    }

    public function testCanUpdateTicket(): void
    {
        // Create a ticket first.
        $payload = json_encode([
            'title' => 'PHP integration test ticket',
            'description' => 'Created during API testing',
            'priority' => 'medium',
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'ignore_errors' => true,
            ],
        ]);

        $response = file_get_contents(
            $this->baseUrl . '/api/tickets',
            false,
            $context
        );

        $this->assertNotFalse($response);

        $data = json_decode($response, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data['item']);

        $ticketId = $data['item']['id'];

        // Update the newly created ticket.
        $payload = json_encode([
            'priority' => 'low',
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'PATCH',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'ignore_errors' => true,
            ],
        ]);

        $response = file_get_contents(
            $this->baseUrl . '/api/tickets/' . $ticketId,
            false,
            $context
        );

        $statusLine = $http_response_header[0] ?? '';

        $this->assertNotFalse($response);
        $this->assertStringContainsString('200', $statusLine);

        $updatedData = json_decode($response, true);

        $this->assertIsArray($updatedData);
        $this->assertSame('low', $updatedData['item']['priority']);
        $this->assertSame($ticketId, $updatedData['item']['id']);
    }

    public function testGetStats(): void
    {
        $response = file_get_contents(
            $this->baseUrl . '/api/tickets/stats',
            false,
            stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'ignore_errors' => true,
                ],
            ])
        );

        $statusLine = $http_response_header[0] ?? '';

        $this->assertNotFalse($response);
        $this->assertStringContainsString('200', $statusLine);

        $data = json_decode($response, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('open', $data);
        $this->assertArrayHasKey('in_progress', $data);
        $this->assertArrayHasKey('closed', $data);
    }
}