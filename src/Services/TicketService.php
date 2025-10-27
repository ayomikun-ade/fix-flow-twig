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
        if (!file_exists(self::TICKETS_FILE)) {
            $demoTickets = [
                new Ticket(
                    'Login page not loading',
                    'open',
                    'Users are reporting that the login page is not loading properly on mobile devices.',
                    'high',
                    'ticket_1',
                    date('Y-m-d H:i:s', strtotime('-2 days')),
                    date('Y-m-d H:i:s', strtotime('-2 days'))
                ),
                new Ticket(
                    'Add dark mode support',
                    'in_progress',
                    'Implement dark mode across the application for better user experience.',
                    'medium',
                    'ticket_2',
                    date('Y-m-d H:i:s', strtotime('-5 days')),
                    date('Y-m-d H:i:s', strtotime('-1 day'))
                ),
                new Ticket(
                    'Password reset email not sent',
                    'open',
                    'Some users are not receiving password reset emails.',
                    'urgent',
                    'ticket_3',
                    date('Y-m-d H:i:s', strtotime('-1 day')),
                    date('Y-m-d H:i:s', strtotime('-1 day'))
                ),
                new Ticket(
                    'Improve dashboard loading speed',
                    'closed',
                    'Dashboard is taking too long to load. Need to optimize queries.',
                    'medium',
                    'ticket_4',
                    date('Y-m-d H:i:s', strtotime('-10 days')),
                    date('Y-m-d H:i:s', strtotime('-3 days'))
                ),
                new Ticket(
                    'Add export functionality',
                    'open',
                    'Users want to export ticket data to CSV/Excel.',
                    'low',
                    'ticket_5',
                    date('Y-m-d H:i:s', strtotime('-7 days')),
                    date('Y-m-d H:i:s', strtotime('-7 days'))
                ),
            ];

            $tickets = array_map(fn($ticket) => $ticket->toArray(), $demoTickets);
            $this->saveTickets($tickets);
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

    public function getAllTickets(): array
    {
        return $this->getTickets();
    }

    public function getTicketById(string $id): ?array
    {
        $tickets = $this->getTickets();

        foreach ($tickets as $ticket) {
            if ($ticket['id'] === $id) {
                return $ticket;
            }
        }

        return null;
    }

    public function createTicket(array $data): Ticket
    {
        $tickets = $this->getTickets();

        $ticket = new Ticket(
            $data['title'],
            $data['status'],
            $data['description'] ?? null,
            $data['priority'] ?? 'medium'
        );

        $tickets[] = $ticket->toArray();
        $this->saveTickets($tickets);

        return $ticket;
    }

    public function updateTicket(string $id, array $data): ?Ticket
    {
        $tickets = $this->getTickets();
        $updated = false;

        foreach ($tickets as $index => $ticket) {
            if ($ticket['id'] === $id) {
                $tickets[$index]['title'] = $data['title'] ?? $ticket['title'];
                $tickets[$index]['description'] = $data['description'] ?? $ticket['description'];
                $tickets[$index]['status'] = $data['status'] ?? $ticket['status'];
                $tickets[$index]['priority'] = $data['priority'] ?? $ticket['priority'];
                $tickets[$index]['updatedAt'] = date('Y-m-d H:i:s');
                $updated = true;

                $this->saveTickets($tickets);
                return Ticket::fromArray($tickets[$index]);
            }
        }

        return null;
    }

    public function deleteTicket(string $id): bool
    {
        $tickets = $this->getTickets();
        $initialCount = count($tickets);

        $tickets = array_filter($tickets, fn($ticket) => $ticket['id'] !== $id);

        if (count($tickets) < $initialCount) {
            $this->saveTickets(array_values($tickets));
            return true;
        }

        return false;
    }

    public function getTicketStats(): array
    {
        $tickets = $this->getTickets();

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

    public function searchTickets(string $query): array
    {
        $tickets = $this->getTickets();

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
