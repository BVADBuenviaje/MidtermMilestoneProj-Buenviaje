<?php
require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../classes/Recipe.php';

$userId = (int)$_SESSION['user_id'];
$recipeId = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if ($recipeId > 0) {
    $recipeModel = new Recipe();
    $recipeModel->delete($recipeId, $userId);
}

header("Location: ../index.php");
exit;
