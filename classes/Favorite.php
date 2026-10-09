<?php
/**
 * Favorite Model
 * Recipe Share - User Recipe Favorites Management
 */

class Favorite {
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
     * Check if a recipe has been favorited by a user.
     */
    public function isFavorited(int $userId, int $recipeId): bool {
        $stmt = $this->pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND recipe_id = ?");
        $stmt->execute([$userId, $recipeId]);
        return (bool)$stmt->fetch();
    }

    /**
     * Toggle favorite status for a recipe.
     * Returns true if added to favorites, false if removed.
     */
    public function toggle(int $userId, int $recipeId): bool {
        if ($this->isFavorited($userId, $recipeId)) {
            $stmt = $this->pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?");
            $stmt->execute([$userId, $recipeId]);
            return false;
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO favorites (user_id, recipe_id) VALUES (?, ?)");
            $stmt->execute([$userId, $recipeId]);
            return true;
        }
    }

    /**
     * Fetch all recipes favorited by a user, joined with author and category details.
     */
    public function getUserFavorites(int $userId): array {
        $sql = "SELECT r.*, u.username, c.name AS category_name, f.created_at AS favorited_at 
                FROM favorites f 
                JOIN recipes r ON f.recipe_id = r.id 
                JOIN users u ON r.user_id = u.id 
                JOIN categories c ON r.category_id = c.id 
                WHERE f.user_id = ? 
                ORDER BY f.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
