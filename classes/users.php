
<?php

class User
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function register(
        string $name,
        string $email,
        string $password
    ): bool {
        // Check whether the email already exists
        $checkSql = "SELECT id FROM users WHERE email = :email";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute(['email' => $email]);

        if ($checkStmt->fetch()) {
            return false;
        }

        // Hash the password before saving it
        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        // Insert the new user
        $sql = "INSERT INTO users (name, email, password, role)
                VALUES (:name, :email, :password, :role)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => 'job_seeker'
        ]);
    }

    public function login(
        string $email,
        string $password
    ): array|false {
        $sql = "SELECT id, name, email, password, role
                FROM users
                WHERE email = :email";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        unset($user['password']);

        return $user;
    }

    public function findById(int $id): array|false
    {
        $sql = "SELECT id, name, email, role
                FROM users
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
