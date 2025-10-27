<?php

namespace App\Services;

use App\Models\Ticket;

class TicketService
{
    private const TICKETS_FILE = __DIR__ . '/../../data/tickets.json';

    public function __construct()
    {
        $this->ensureDataDirectory();
        $this->initializeDemoTickets();
    }

    private function ensureDataDirectory(): void
    {
        $dataDir = dirname(self::TICKETS_FILE);
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0777, true);
        }
    }

    private function initializeDemoTickets(): void
    {
        // Demo tickets are no longer auto-created
        // Users will create their own tickets
        if (!file_exists(self::TICKETS_FILE)) {
            $this->saveTickets([]);
        }
    }

    private function getTickets(): array
    {
        if (!file_exists(self::TICKETS_FILE)) {
            return [];
        }

        $content = file_get_contents(self::TICKETS_FILE);
        return json_decode($content, true) ?? [];
    }

    private function saveTickets(array $tickets): void
    {
        file_put_contents(self::TICKETS_FILE, json_encode($tickets, JSON_PRETTY_PRINT));
    }

    public function getAllTickets(?string $userId = null): array
    {
        $tickets = $this->getTickets();

        if ($userId === null) {
            return $tickets;
        }

        return array_filter($tickets, fn($ticket) => $ticket['userId'] === $userId);
    }

    public function getTicketById(string $id, ?string $userId = null): ?array
    {
        $tickets = $this->getTickets();

        foreach ($tickets as $ticket) {
            if ($ticket['id'] === $id) {
                // If userId is provided, verify ownership
                if ($userId !== null && $ticket['userId'] !== $userId) {
                    return null;
                }
                return $ticket;
            }
        }

        return null;
    }

    public function createTicket(array $data, string $userId): Ticket
    {
        $tickets = $this->getTickets();

        $ticket = new Ticket(
            $data['title'],
            $data['status'],
            $userId,
            $data['description'] ?? null,
            $data['priority'] ?? 'medium'
        );

        $tickets[] = $ticket->toArray();
        $this->saveTickets($tickets);

        return $ticket;
    }

    public function updateTicket(string $id, array $data, string $userId): ?Ticket
    {
        $tickets = $this->getTickets();

        foreach ($tickets as $index => $ticket) {
            if ($ticket['id'] === $id) {
                // Verify ownership
                if ($ticket['userId'] !== $userId) {
                    return null;
                }

                $tickets[$index]['title'] = $data['title'] ?? $ticket['title'];
                $tickets[$index]['description'] = $data['description'] ?? $ticket['description'];
                $tickets[$index]['status'] = $data['status'] ?? $ticket['status'];
                $tickets[$index]['priority'] = $data['priority'] ?? $ticket['priority'];
                $tickets[$index]['updatedAt'] = date('Y-m-d H:i:s');

                $this->saveTickets($tickets);
                return Ticket::fromArray($tickets[$index]);
            }
        }

        return null;
    }

    public function deleteTicket(string $id, string $userId): bool
    {
        $tickets = $this->getTickets();
        $deleted = false;

        foreach ($tickets as $index => $ticket) {
            if ($ticket['id'] === $id) {
                // Verify ownership
                if ($ticket['userId'] !== $userId) {
                    return false;
                }
                unset($tickets[$index]);
                $deleted = true;
                break;
            }
        }

        if ($deleted) {
            $this->saveTickets(array_values($tickets));
            return true;
        }

        return false;
    }

    public function getTicketStats(?string $userId = null): array
    {
        $tickets = $this->getAllTickets($userId);

        $stats = [
            'total' => count($tickets),
            'open' => 0,
            'in_progress' => 0,
            'closed' => 0
        ];

        foreach ($tickets as $ticket) {
            $status = $ticket['status'];
            if (isset($stats[$status])) {
                $stats[$status]++;
            }
        }

        return $stats;
    }

    public function searchTickets(string $query, ?string $userId = null): array
    {
        $tickets = $this->getAllTickets($userId);

        if (empty($query)) {
            return $tickets;
        }

        $query = strtolower($query);

        return array_filter($tickets, function ($ticket) use ($query) {
            return str_contains(strtolower($ticket['title']), $query) ||
                   str_contains(strtolower($ticket['description'] ?? ''), $query) ||
                   str_contains(strtolower($ticket['status']), $query);
        });
    }
}
