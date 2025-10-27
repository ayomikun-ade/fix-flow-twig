<?php

namespace App\Models;

class Ticket
{
    public string $id;
    public string $title;
    public ?string $description;
    public string $status;
    public ?string $priority;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        string $title,
        string $status,
        ?string $description = null,
        ?string $priority = null,
        ?string $id = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id ?? uniqid('ticket_', true);
        $this->title = $title;
        $this->description = $description;
        $this->status = $status;
        $this->priority = $priority ?? 'medium';
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->updatedAt = $updatedAt ?? date('Y-m-d H:i:s');
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['title'],
            $data['status'],
            $data['description'] ?? null,
            $data['priority'] ?? null,
            $data['id'] ?? null,
            $data['createdAt'] ?? null,
            $data['updatedAt'] ?? null
        );
    }
}
