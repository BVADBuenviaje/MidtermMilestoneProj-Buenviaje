<?php
require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../classes/Comment.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipeId = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : 0;
    $content  = trim($_POST['content'] ?? '');
    $userId   = (int)$_SESSION['user_id'];

    if ($recipeId > 0 && $content !== '') {
        $commentModel = new Comment();
        $commentModel->create($recipeId, $userId, $content);
        header("Location: ../recipes/view.php?id=" . $recipeId);
        exit;
    }

    if ($recipeId > 0) {
        header("Location: ../recipes/view.php?id=" . $recipeId . "&error=empty_comment");
        exit;
    }
}

header("Location: ../index.php");
exit;
