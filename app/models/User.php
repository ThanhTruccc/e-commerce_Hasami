<?php
require_once CORE_PATH . '/Model.php';

// ============================================================
//  MODEL: User.php
// ============================================================

class User extends Model {

    protected string $table = 'users';

    public function findByEmail(string $email): array|false {
        return $this->query(
            "SELECT * FROM users WHERE email = :email LIMIT 1",
            [':email' => $email]
        )->fetch();
    }

    public function register(array $data): int|false {
        if ($this->findByEmail($data['email'])) return false;
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        return $this->insert($data);
    }

    public function login(string $email, string $password): array|false {
        $user = $this->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) return false;
        if (!$user['is_active']) return false;
        return $user;
    }

    public function updateProfile(int $id, array $data): bool {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']);
        }
        return $this->update($id, $data);
    }

    public function getAll(int $page = 1): array {
        $offset = ($page - 1) * 20;
        $data   = $this->query(
            "SELECT id, name, email, phone, skin_type, role, is_active, created_at
             FROM users ORDER BY created_at DESC LIMIT 20 OFFSET {$offset}"
        )->fetchAll();
        $total = $this->count();
        return compact('data', 'total');
    }
}
