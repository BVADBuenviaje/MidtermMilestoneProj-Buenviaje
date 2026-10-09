<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../classes/Favorite.php';

$userId = (int)$_SESSION['user_id'];
$recipeId = 0;

// Read recipe_id from standard POST or JSON body
if (isset($_POST['recipe_id'])) {
    $recipeId = (int)$_POST['recipe_id'];
} else {
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $jsonData = json_decode($rawInput, true);
        if (isset($jsonData['recipe_id'])) {
            $recipeId = (int)$jsonData['recipe_id'];
        }
    }
}

if ($recipeId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid recipe ID']);
    exit;
}

try {
    $favoriteModel = new Favorite();
    $isFavorited = $favoriteModel->toggle($userId, $recipeId);

    echo json_encode([
        'success'      => true,
        'is_favorited' => $isFavorited
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
exit;
