<?php
require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../classes/Comment.php';

$commentModel = new Comment();
$userId = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commentId = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
    $recipeId  = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : 0;
    $content   = trim($_POST['content'] ?? '');

    if ($commentId > 0 && $content !== '') {
        $comment = $commentModel->getById($commentId);
        if ($comment && (int)$comment['user_id'] === $userId) {
            $commentModel->update($commentId, $userId, $content);
            $redirectRecipeId = $recipeId > 0 ? $recipeId : (int)$comment['recipe_id'];
            header("Location: ../recipes/view.php?id=" . $redirectRecipeId);
            exit;
        }
    }

    if ($recipeId > 0) {
        header("Location: ../recipes/view.php?id=" . $recipeId);
        exit;
    }
    header("Location: ../index.php");
    exit;
}

// GET Request: Render edit comment form
$commentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$comment = $commentModel->getById($commentId);

if (!$comment || (int)$comment['user_id'] !== $userId) {
    header("Location: ../index.php");
    exit;
}

$recipeId = (int)$comment['recipe_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Comment - Recipe Share</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/tailwind.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>
<body class="bg-[#fcf9f6] min-h-screen text-stone-800 antialiased p-6 flex items-center justify-center">
    <div class="max-w-lg w-full bg-white p-6 rounded-2xl shadow-sm border border-amber-100">
        <h1 class="text-xl font-bold text-stone-900 mb-4">Edit Comment</h1>

        <form method="POST" action="edit.php" class="space-y-4">
            <input type="hidden" name="comment_id" value="<?= htmlspecialchars((string)$comment['id']) ?>">
            <input type="hidden" name="recipe_id" value="<?= htmlspecialchars((string)$recipeId) ?>">

            <div>
                <label for="content" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Comment</label>
                <textarea id="content" name="content" rows="4" required style="border: 1px solid #cbd5e1; background-color: #ffffff; color: #1e293b; padding: 8px; border-radius: 6px; width: 100%;"><?= htmlspecialchars($comment['content']) ?></textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" style="background-color: #d97706; color: #ffffff; padding: 8px 16px; border-radius: 6px; font-weight: bold; text-decoration: none; border: none; cursor: pointer;">
                    Save Changes
                </button>
                <a href="../recipes/view.php?id=<?= htmlspecialchars((string)$recipeId) ?>" class="text-sm text-stone-500 hover:text-stone-700 underline">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</body>
</html>
