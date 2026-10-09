<?php
/**
 * Comment Model
 * Recipe Share - Comment Management with PDO Transactions
 */

class Comment {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        if ($pdo !== null) {
            $this->pdo = $pdo;
        } else {
            require_once __DIR__ . '/../config/db.php';
            $this->pdo = $GLOBALS['pdo'] ?? ($pdo ?? null);
        }
    }

    /**
     * Fetch comments for a recipe joined with author usernames, ordered by created_at ASC.
     */
    public function getByRecipe(int $recipeId): array {
        $sql = "SELECT c.*, u.username 
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.recipe_id = ? 
                ORDER BY c.created_at ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    /**
     * Fetch single comment details.
     */
    public function getById(int $commentId): ?array {
        $sql = "SELECT c.*, u.username 
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$commentId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Create a comment inside a PDO transaction.
     */
    public function create(int $recipeId, int $userId, string $content): bool {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "INSERT INTO comments (recipe_id, user_id, content) VALUES (?, ?, ?)"
            );
            $stmt->execute([$recipeId, $userId, $content]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Update comment content and set is_edited = 1 inside a PDO transaction.
     * Verifies author ownership before making changes.
     */
    public function update(int $commentId, int $userId, string $content): bool {
        // Ownership check
        $check = $this->pdo->prepare("SELECT user_id FROM comments WHERE id = ?");
        $check->execute([$commentId]);
        $row = $check->fetch();

        if (!$row || (int)$row['user_id'] !== $userId) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "UPDATE comments SET content = ?, is_edited = 1 WHERE id = ? AND user_id = ?"
            );
            $stmt->execute([$content, $commentId, $userId]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Delete a comment inside a PDO transaction.
     * Verifies author ownership before deletion.
     */
    public function delete(int $commentId, int $userId): bool {
        // Ownership check
        $check = $this->pdo->prepare("SELECT user_id FROM comments WHERE id = ?");
        $check->execute([$commentId]);
        $row = $check->fetch();

        if (!$row || (int)$row['user_id'] !== $userId) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
            $stmt->execute([$commentId, $userId]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
