<?php

namespace App\Models;

class User
{
    public string $id;
    public string $email;
    public string $name;
    public string $password;

    public function __construct(string $email, string $name, string $password, ?string $id = null)
    {
        $this->id = $id ?? uniqid('user_', true);
        $this->email = $email;
        $this->name = $name;
        $this->password = $password;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'password' => $this->password
        ];
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['email'],
            $data['name'],
            $data['password'],
            $data['id'] ?? null
        );
    }
}
