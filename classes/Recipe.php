<?php
/**
 * Recipe Model
 * Recipe Share - Recipe Management with PDO Transactions
 */

class Recipe {
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
     * Fetch recipes joined with users and categories, ordered by created_at DESC.
     * Supports filtering by keyword and categoryId.
     */
    public function getAll(?string $keyword = null, ?int $categoryId = null): array {
        $sql = "SELECT r.*, u.username, c.name AS category_name 
                FROM recipes r 
                JOIN users u ON r.user_id = u.id 
                JOIN categories c ON r.category_id = c.id";
        
        $conditions = [];
        $params = [];

        if ($categoryId !== null && $categoryId > 0) {
            $conditions[] = "r.category_id = ?";
            $params[] = $categoryId;
        }

        if ($keyword !== null && trim($keyword) !== '') {
            $conditions[] = "(r.title LIKE ? OR r.description LIKE ?)";
            $term = '%' . trim($keyword) . '%';
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY r.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Fetch single recipe details joined with user and category.
     */
    public function getById(int $id): ?array {
        $sql = "SELECT r.*, u.username, c.name AS category_name 
                FROM recipes r 
                JOIN users u ON r.user_id = u.id 
                JOIN categories c ON r.category_id = c.id 
                WHERE r.id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $recipe = $stmt->fetch();
        return $recipe ?: null;
    }

    /**
     * Fetch rows from ingredients for a given recipe.
     */
    public function getIngredients(int $recipeId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM ingredients WHERE recipe_id = ? ORDER BY id ASC");
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    /**
     * Create a recipe and its ingredients wrapped inside a PDO transaction.
     */
    public function create(
        int $userId,
        int $categoryId,
        string $title,
        string $description,
        string $instructions,
        int $cookingTime,
        int $servings,
        array $ingredients
    ): int {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "INSERT INTO recipes (user_id, category_id, title, description, instructions, cooking_time_mins, servings) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $userId,
                $categoryId,
                $title,
                $description,
                $instructions,
                $cookingTime,
                $servings
            ]);

            $recipeId = (int)$this->pdo->lastInsertId();

            $ingStmt = $this->pdo->prepare("INSERT INTO ingredients (recipe_id, item) VALUES (?, ?)");
            foreach ($ingredients as $item) {
                $itemText = trim((string)$item);
                if ($itemText !== '') {
                    $ingStmt->execute([$recipeId, $itemText]);
                }
            }

            $this->pdo->commit();
            return $recipeId;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Update a recipe and re-insert its ingredients inside a PDO transaction.
     * Verifies ownership before performing changes.
     */
    public function update(
        int $recipeId,
        int $userId,
        int $categoryId,
        string $title,
        string $description,
        string $instructions,
        int $cookingTime,
        int $servings,
        array $ingredients
    ): bool {
        // Verify ownership
        $check = $this->pdo->prepare("SELECT user_id FROM recipes WHERE id = ?");
        $check->execute([$recipeId]);
        $recipe = $check->fetch();

        if (!$recipe || (int)$recipe['user_id'] !== $userId) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // Update recipe details and mark is_edited = 1
            $stmt = $this->pdo->prepare(
                "UPDATE recipes 
                 SET category_id = ?, title = ?, description = ?, instructions = ?, cooking_time_mins = ?, servings = ?, is_edited = 1 
                 WHERE id = ? AND user_id = ?"
            );
            $stmt->execute([
                $categoryId,
                $title,
                $description,
                $instructions,
                $cookingTime,
                $servings,
                $recipeId,
                $userId
            ]);

            // Delete existing ingredients
            $delStmt = $this->pdo->prepare("DELETE FROM ingredients WHERE recipe_id = ?");
            $delStmt->execute([$recipeId]);

            // Re-insert updated ingredients
            $ingStmt = $this->pdo->prepare("INSERT INTO ingredients (recipe_id, item) VALUES (?, ?)");
            foreach ($ingredients as $item) {
                $itemText = trim((string)$item);
                if ($itemText !== '') {
                    $ingStmt->execute([$recipeId, $itemText]);
                }
            }

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
     * Delete a recipe inside a PDO transaction.
     * Verifies ownership before deleting.
     */
    public function delete(int $recipeId, int $userId): bool {
        // Verify ownership
        $check = $this->pdo->prepare("SELECT user_id FROM recipes WHERE id = ?");
        $check->execute([$recipeId]);
        $recipe = $check->fetch();

        if (!$recipe || (int)$recipe['user_id'] !== $userId) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // Delete ingredients first (if not handled by cascade)
            $delIng = $this->pdo->prepare("DELETE FROM ingredients WHERE recipe_id = ?");
            $delIng->execute([$recipeId]);

            // Delete favorites for this recipe
            $delFav = $this->pdo->prepare("DELETE FROM favorites WHERE recipe_id = ?");
            $delFav->execute([$recipeId]);

            // Delete recipe
            $stmt = $this->pdo->prepare("DELETE FROM recipes WHERE id = ? AND user_id = ?");
            $stmt->execute([$recipeId, $userId]);

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
     * Fetch all categories ordered by id.
     */
    public function getCategories(): array {
        $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY id ASC");
        return $stmt->fetchAll();
    }
}
