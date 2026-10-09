<?php
/**
 * User Model
 * Recipe Share - User Management & Authentication
 */

class User {
    private ?PDO $pdo = null;

    public function __construct(?PDO $pdo = null) {
        if ($pdo !== null) {
            $this->pdo = $pdo;
        } else {
            require_once __DIR__ . '/../config/db.php';
            $this->pdo = $GLOBALS['pdo'] ?? ($pdo ?? null);
        }
    }

    /**
     * Helper to retrieve PDO instance for static calls
     */
    private static function getPdo(?PDO $pdo = null): PDO {
        if ($pdo !== null) {
            return $pdo;
        }
        require_once __DIR__ . '/../config/db.php';
        return $GLOBALS['pdo'] ?? ($pdo ?? null);
    }

    /**
     * Register a new user inside a PDO transaction.
     * Returns the created user's ID.
     *
     * @throws Exception if username or email already exists or insertion fails
     */
    public static function register(string $username, string $email, string $password, ?PDO $pdo = null): int {
        $db = self::getPdo($pdo);

        // Check if username or email is already taken
        $check = $db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $check->execute([$email, $username]);
        if ($check->fetch()) {
            throw new Exception("Username or email already exists.");
        }

        // Native secure password hashing
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword]);

            $newUserId = (int)$db->lastInsertId();

            $db->commit();
            return $newUserId;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Authenticate a user by email and password using password_verify().
     * Returns the user array on success, or null on invalid credentials.
     */
    public static function login(string $email, string $password, ?PDO $pdo = null): ?array {
        $db = self::getPdo($pdo);

        $stmt = $db->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return null;
    }

    /**
     * Find a user record by ID.
     */
    public static function findById(int $id, ?PDO $pdo = null): ?array {
        $db = self::getPdo($pdo);
        $stmt = $db->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find a user record by Email.
     */
    public static function findByEmail(string $email, ?PDO $pdo = null): ?array {
        $db = self::getPdo($pdo);
        $stmt = $db->prepare("SELECT id, username, email, created_at FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }
}
