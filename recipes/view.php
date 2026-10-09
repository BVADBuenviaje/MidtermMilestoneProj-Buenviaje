<?php
require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../classes/Recipe.php';
require_once __DIR__ . '/../classes/Favorite.php';
require_once __DIR__ . '/../classes/Comment.php';

$recipeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$recipeModel = new Recipe();
$recipe = $recipeModel->getById($recipeId);

if (!$recipe) {
    header("Location: ../index.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$isOwner = ((int)$recipe['user_id'] === $userId);

$ingredients   = $recipeModel->getIngredients($recipeId);
$favoriteModel = new Favorite();
$isFavorited   = $favoriteModel->isFavorited($userId, $recipeId);

$commentModel  = new Comment();
$comments      = $commentModel->getByRecipe($recipeId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($recipe['title']) ?> - Recipe Share</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/tailwind.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>
<body class="bg-[#fcf9f6] min-h-screen text-stone-800 antialiased p-4 sm:p-8">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Top Nav Bar -->
        <div class="flex items-center justify-between bg-white px-6 py-4 rounded-2xl shadow-sm border border-amber-100">
            <a href="../index.php" class="inline-flex items-center gap-2 text-sm text-stone-600 hover:text-amber-800 font-medium transition">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Recipes</span>
            </a>
            <div class="flex items-center gap-3">
                <span class="text-xs text-stone-500 hidden sm:inline">Signed in as <strong><?= htmlspecialchars($_SESSION['username'] ?? '') ?></strong></span>
                <a href="../auth/logout.php" class="text-xs text-stone-500 hover:text-red-600">Logout</a>
            </div>
        </div>

        <!-- Recipe Main Card -->
        <article class="bg-white p-6 sm:p-10 rounded-2xl shadow-sm border border-amber-100 space-y-6">
            <!-- Header with Title & Action Controls -->
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 pb-6 border-b border-stone-200">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span style="background-color: #fef3c7; color: #92400e; font-size: 11px; padding: 2px 6px; border-radius: 4px;">
                            <?= htmlspecialchars($recipe['category_name']) ?>
                        </span>
                        <?php if ((int)$recipe['is_edited'] === 1): ?>
                            <span style="background-color: #fef3c7; color: #92400e; font-size: 11px; padding: 2px 6px; border-radius: 4px;">
                                (edited)
                            </span>
                        <?php endif; ?>
                    </div>
                    <h1 class="text-3xl font-extrabold text-stone-900 tracking-tight">
                        <?= htmlspecialchars($recipe['title']) ?>
                    </h1>
                    <p class="text-xs text-stone-500">
                        Posted by <strong class="text-stone-700"><?= htmlspecialchars($recipe['username']) ?></strong>
                        &bull; <?= htmlspecialchars(date('M j, Y', strtotime($recipe['created_at']))) ?>
                    </p>
                </div>

                <!-- Author Actions & Favorite Button -->
                <div class="flex items-center gap-2 shrink-0">
                    <!-- Favorite Toggle Button -->
                    <button type="button" 
                            class="fav-btn flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-stone-200 hover:bg-stone-50 transition cursor-pointer"
                            data-recipe-id="<?= htmlspecialchars((string)$recipe['id']) ?>"
                            title="Toggle Favorite">
                        <i class="<?= $isFavorited ? 'fa-solid text-red-500' : 'fa-regular text-stone-400' ?> fa-heart text-base"
                           style="color: <?= $isFavorited ? '#ef4444' : '#94a3b8' ?>;"></i>
                        <span class="fav-text text-xs font-medium text-stone-700">
                            <?= $isFavorited ? 'Favorited' : 'Favorite' ?>
                        </span>
                    </button>

                    <?php if ($isOwner): ?>
                        <!-- Recipe Author Only: Edit & Delete -->
                        <a href="edit.php?id=<?= htmlspecialchars((string)$recipe['id']) ?>" 
                           style="background-color: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 4px; font-weight: 600; text-decoration: none;">
                            <i class="fa-solid fa-pen-to-square text-xs mr-1"></i> Edit
                        </a>

                        <form method="POST" action="delete.php" class="inline" onsubmit="return confirm('Are you sure you want to permanently delete this recipe?');">
                            <input type="hidden" name="recipe_id" value="<?= htmlspecialchars((string)$recipe['id']) ?>">
                            <button type="submit" style="background-color: #fee2e2; color: #b91c1c; padding: 4px 10px; border-radius: 4px; font-weight: 600; border: none; cursor: pointer;">
                                <i class="fa-solid fa-trash text-xs mr-1"></i> Delete
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Meta Badges -->
            <div class="flex flex-wrap gap-3 py-2">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-100 text-stone-700 text-xs">
                    <i class="fa-regular fa-clock text-amber-700"></i>
                    <span>Cooking Time: <strong><?= htmlspecialchars((string)$recipe['cooking_time_mins']) ?> mins</strong></span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-100 text-stone-700 text-xs">
                    <i class="fa-solid fa-users text-amber-700"></i>
                    <span>Servings: <strong><?= htmlspecialchars((string)$recipe['servings']) ?> portions</strong></span>
                </div>
            </div>

            <!-- Description -->
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-stone-500 mb-2">Description</h2>
                <p class="text-stone-700 leading-relaxed text-sm bg-stone-50/70 p-4 rounded-xl border border-stone-100">
                    <?= nl2br(htmlspecialchars($recipe['description'])) ?>
                </p>
            </div>

            <!-- Ingredients -->
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-stone-500 mb-3">Ingredients</h2>
                <?php if (empty($ingredients)): ?>
                    <p class="text-sm text-stone-400 italic">No ingredients listed.</p>
                <?php else: ?>
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-stone-700">
                        <?php foreach ($ingredients as $ing): ?>
                            <li class="flex items-center gap-2 bg-stone-50 px-3 py-2 rounded-lg border border-stone-100">
                                <i class="fa-solid fa-check text-xs text-amber-600"></i>
                                <span><?= htmlspecialchars($ing['item']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Instructions -->
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-stone-500 mb-3">Cooking Instructions</h2>
                <div class="prose max-w-none text-stone-700 text-sm leading-relaxed whitespace-pre-line bg-amber-50/30 p-5 rounded-xl border border-amber-100/60 font-sans">
                    <?= htmlspecialchars($recipe['instructions']) ?>
                </div>
            </div>
        </article>

        <!-- Comments Section -->
        <section class="bg-white p-6 sm:p-10 rounded-2xl shadow-sm border border-amber-100 space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-stone-200">
                <h2 class="text-xl font-bold text-stone-900 flex items-center gap-2">
                    <i class="fa-regular fa-comments text-amber-700"></i>
                    <span>Discussion (<?= count($comments) ?>)</span>
                </h2>
            </div>

            <!-- Comments List -->
            <div class="space-y-4">
                <?php if (empty($comments)): ?>
                    <p class="text-sm text-stone-400 italic py-2">No comments yet. Be the first to share your thoughts!</p>
                <?php else: ?>
                    <?php foreach ($comments as $com): ?>
                        <?php $isCommentAuthor = ((int)$com['user_id'] === $userId); ?>
                        <div class="p-4 rounded-xl bg-stone-50 border border-stone-200/80 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <strong class="text-stone-800"><?= htmlspecialchars($com['username']) ?></strong>
                                    <span class="text-stone-400">&bull; <?= htmlspecialchars(date('M j, Y g:i a', strtotime($com['created_at']))) ?></span>
                                    <?php if ((int)$com['is_edited'] === 1): ?>
                                        <span style="background-color: #fef3c7; color: #92400e; font-size: 11px; padding: 2px 6px; border-radius: 4px;">
                                            (edited)
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($isCommentAuthor): ?>
                                    <!-- Comment Author Actions -->
                                    <div class="flex items-center gap-2">
                                        <button type="button" 
                                                onclick="toggleCommentEdit(<?= htmlspecialchars((string)$com['id']) ?>)"
                                                style="background-color: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 4px; font-weight: 600; text-decoration: none; border: none; cursor: pointer;">
                                            Edit
                                        </button>

                                        <form method="POST" action="../comments/delete.php" class="inline" onsubmit="return confirm('Delete this comment?');">
                                            <input type="hidden" name="comment_id" value="<?= htmlspecialchars((string)$com['id']) ?>">
                                            <input type="hidden" name="recipe_id" value="<?= htmlspecialchars((string)$recipe['id']) ?>">
                                            <button type="submit" style="background-color: #fee2e2; color: #b91c1c; padding: 4px 10px; border-radius: 4px; font-weight: 600; border: none; cursor: pointer;">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Comment Content Display -->
                            <p id="comment-text-<?= htmlspecialchars((string)$com['id']) ?>" class="text-sm text-stone-700 whitespace-pre-line leading-relaxed">
                                <?= htmlspecialchars($com['content']) ?>
                            </p>

                            <?php if ($isCommentAuthor): ?>
                                <!-- Inline Comment Edit Form (hidden by default) -->
                                <form id="comment-edit-form-<?= htmlspecialchars((string)$com['id']) ?>" 
                                      method="POST" action="../comments/edit.php" 
                                      class="hidden pt-2 space-y-2">
                                    <input type="hidden" name="comment_id" value="<?= htmlspecialchars((string)$com['id']) ?>">
                                    <input type="hidden" name="recipe_id" value="<?= htmlspecialchars((string)$recipe['id']) ?>">
                                    <textarea name="content" rows="3" required style="border: 1px solid #cbd5e1; background-color: #ffffff; color: #1e293b; padding: 8px; border-radius: 6px; width: 100%;"><?= htmlspecialchars($com['content']) ?></textarea>
                                    <div class="flex items-center gap-2">
                                        <button type="submit" style="background-color: #d97706; color: #ffffff; padding: 8px 16px; border-radius: 6px; font-weight: bold; text-decoration: none; border: none; cursor: pointer;">
                                            Save
                                        </button>
                                        <button type="button" 
                                                onclick="toggleCommentEdit(<?= htmlspecialchars((string)$com['id']) ?>)" 
                                                class="text-xs text-stone-500 hover:text-stone-700 underline cursor-pointer">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- New Comment Form -->
            <div class="pt-6 border-t border-stone-200">
                <h3 class="text-sm font-bold text-stone-800 mb-3">Leave a Comment</h3>
                <form method="POST" action="../comments/create.php" class="space-y-3">
                    <input type="hidden" name="recipe_id" value="<?= htmlspecialchars((string)$recipe['id']) ?>">
                    <div>
                        <textarea name="content" rows="3" required placeholder="Write your tips, questions, or variations on this recipe..." style="border: 1px solid #cbd5e1; background-color: #ffffff; color: #1e293b; padding: 8px; border-radius: 6px; width: 100%;"></textarea>
                    </div>
                    <button type="submit" style="background-color: #d97706; color: #ffffff; padding: 8px 16px; border-radius: 6px; font-weight: bold; text-decoration: none; border: none; cursor: pointer;">
                        Post Comment
                    </button>
                </form>
            </div>
        </section>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/favorites.js"></script>
    <script>
        function toggleCommentEdit(id) {
            const textEl = document.getElementById('comment-text-' + id);
            const formEl = document.getElementById('comment-edit-form-' + id);
            if (!textEl || !formEl) return;
            if (formEl.classList.contains('hidden')) {
                formEl.classList.remove('hidden');
                textEl.classList.add('hidden');
            } else {
                formEl.classList.add('hidden');
                textEl.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>
