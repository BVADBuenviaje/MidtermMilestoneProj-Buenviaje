<?php
require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../classes/Comment.php';

$commentModel = new Comment();
$userId = (int)$_SESSION['user_id'];

$commentId = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
$recipeId  = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : (isset($_GET['recipe_id']) ? (int)$_GET['recipe_id'] : 0);

if ($commentId > 0) {
    $comment = $commentModel->getById($commentId);
    if ($comment) {
        if ($recipeId <= 0) {
            $recipeId = (int)$comment['recipe_id'];
        }
        if ((int)$comment['user_id'] === $userId) {
            $commentModel->delete($commentId, $userId);
        }
    }
}

if ($recipeId > 0) {
    header("Location: ../recipes/view.php?id=" . $recipeId);
    exit;
}

header("Location: ../index.php");
exit;
