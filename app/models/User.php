<?php
declare(strict_types=1);

namespace Models;

use Core\BaseModel;
use PDO;

class User extends BaseModel
{
    private ?bool $hasIsActiveColumn = null;

    private function hasIsActiveColumn(): bool
    {
        if ($this->hasIsActiveColumn !== null) {
            return $this->hasIsActiveColumn;
        }

        $stmt = $this->db->prepare("SHOW COLUMNS FROM users LIKE 'is_active'");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->hasIsActiveColumn = (bool)$row;
        return $this->hasIsActiveColumn;
    }

    public function findById(int $id): ?array
    {
        $isActiveSelect = $this->hasIsActiveColumn()
            ? 'u.is_active'
            : '1 AS is_active';

        $stmt = $this->db->prepare(
            'SELECT u.*, ' . $isActiveSelect . ', r.name AS role
             FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $isActiveSelect = $this->hasIsActiveColumn()
            ? 'u.is_active'
            : '1 AS is_active';

        $stmt = $this->db->prepare(
            'SELECT u.*, ' . $isActiveSelect . ', r.name AS role
             FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.email = :email
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function create(array $data): int
    {
        $role = $data['role'] ?? 'visitor';

        $roleStmt = $this->db->prepare('SELECT id FROM roles WHERE name = :role LIMIT 1');
        $roleStmt->execute(['role' => $role]);
        $roleRow = $roleStmt->fetch(PDO::FETCH_ASSOC);
        if (!$roleRow) {
            throw new \RuntimeException('Invalid role.');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password, role_id, created_at)
             VALUES (:name, :email, :password, :role_id, NOW())'
        );
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role_id' => (int)$roleRow['id'],
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateProfile(int $id, array $data): void
    {
        if (!empty($data['password'])) {
            $stmt = $this->db->prepare(
                'UPDATE users
                 SET name = :name, email = :email, password = :password
                 WHERE id = :id'
            );
            $stmt->execute([
                'id' => $id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);
            return;
        }

        $stmt = $this->db->prepare(
            'UPDATE users
             SET name = :name, email = :email
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
    }

    public function getAllWithRoles(): array
    {
        if ($this->hasIsActiveColumn()) {
            $stmt = $this->db->query(
                'SELECT u.id, u.name, u.email, u.is_active, u.created_at, r.name AS role
                 FROM users u
                 JOIN roles r ON u.role_id = r.id
                 ORDER BY u.created_at DESC'
            );
        } else {
            $stmt = $this->db->query(
                'SELECT u.id, u.name, u.email, 1 AS is_active, u.created_at, r.name AS role
                 FROM users u
                 JOIN roles r ON u.role_id = r.id
                 ORDER BY u.created_at DESC'
            );
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateRole(int $userId, string $role): void
    {
        $roleStmt = $this->db->prepare('SELECT id FROM roles WHERE name = :role LIMIT 1');
        $roleStmt->execute(['role' => $role]);
        $roleRow = $roleStmt->fetch(PDO::FETCH_ASSOC);
        if (!$roleRow) {
            throw new \RuntimeException('Invalid role.');
        }

        $stmt = $this->db->prepare('UPDATE users SET role_id = :role_id WHERE id = :id');
        $stmt->execute([
            'role_id' => (int)$roleRow['id'],
            'id' => $userId,
        ]);
    }

    public function setActiveStatus(int $userId, bool $isActive): void
    {
        // Backward compatibility for old schema without users.is_active.
        if (!$this->hasIsActiveColumn()) {
            return;
        }

        $stmt = $this->db->prepare('UPDATE users SET is_active = :is_active WHERE id = :id');
        $stmt->execute([
            'is_active' => $isActive ? 1 : 0,
            'id' => $userId,
        ]);
    }
}

