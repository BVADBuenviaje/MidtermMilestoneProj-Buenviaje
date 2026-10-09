<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/classes/Recipe.php';
require_once __DIR__ . '/classes/Favorite.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userId     = $isLoggedIn ? (int)$_SESSION['user_id'] : 0;
$username   = $_SESSION['username'] ?? '';

$recipeModel   = new Recipe();
$favoriteModel = new Favorite();
$categories    = $recipeModel->getCategories();

$keyword       = trim($_GET['search'] ?? '');
$categoryId    = (isset($_GET['category']) && $_GET['category'] !== '') ? (int)$_GET['category'] : null;
$viewFavorites = ($isLoggedIn && isset($_GET['view']) && $_GET['view'] === 'favorites');

// Fetch user's favorited recipe IDs for instant heart rendering
$userFavoritedIds = [];
if ($isLoggedIn) {
    $userFavs = $favoriteModel->getUserFavorites($userId);
    foreach ($userFavs as $uf) {
        $userFavoritedIds[(int)$uf['id']] = true;
    }
}

// Fetch recipes based on feed view or favorites view
if ($viewFavorites) {
    $rawFavs = $favoriteModel->getUserFavorites($userId);
    // Apply client-side keyword and category filters to favorites if specified
    $recipes = array_filter($rawFavs, function ($r) use ($keyword, $categoryId) {
        if ($categoryId !== null && $categoryId > 0 && (int)$r['category_id'] !== $categoryId) {
            return false;
        }
        if ($keyword !== '') {
            $term = mb_strtolower($keyword);
            $inTitle = mb_stripos($r['title'], $term) !== false;
            $inDesc  = mb_stripos($r['description'], $term) !== false;
            if (!$inTitle && !$inDesc) {
                return false;
            }
        }
        return true;
    });
} else {
    $recipes = $recipeModel->getAll($keyword !== '' ? $keyword : null, $categoryId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Share - Community Cookbook</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/tailwind.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
</head>
<body class="bg-[#fcf9f6] min-h-screen text-stone-800 antialiased flex flex-col">
    <!-- Navbar -->
    <header class="bg-white border-b border-amber-100 shadow-sm sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-2.5 group">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-[#d97706] group-hover:bg-[#d97706] group-hover:text-white transition">
                    <i class="fa-solid fa-utensils text-lg"></i>
                </div>
                <span class="font-bold text-xl tracking-tight text-stone-900 group-hover:text-[#d97706] transition">Recipe Share</span>
            </a>

            <nav class="flex items-center gap-3">
                <?php if ($isLoggedIn): ?>
                    <a href="recipes/create.php" 
                       style="background-color: #d97706; color: #ffffff; padding: 8px 16px; border-radius: 6px; font-weight: bold; text-decoration: none;">
                        <i class="fa-solid fa-plus mr-1 text-xs"></i> New Recipe
                    </a>
                    <span class="text-xs text-stone-500 hidden md:inline ml-2">
                        Hi, <strong class="text-stone-800"><?= htmlspecialchars($username) ?></strong>
                    </span>
                    <a href="auth/logout.php" 
                       class="text-xs text-stone-500 hover:text-red-600 px-2 py-1 rounded transition">
                        Logout
                    </a>
                <?php else: ?>
                    <a href="auth/login.php" class="px-4 py-2 text-sm font-medium text-stone-700 hover:text-[#d97706] transition">
                        Sign In
                    </a>
                    <a href="auth/register.php" 
                       style="background-color: #d97706; color: #ffffff; padding: 8px 16px; border-radius: 6px; font-weight: bold; text-decoration: none;">
                        Create Account
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-6xl w-full mx-auto px-4 py-8 space-y-8">
        <!-- Search, Filter & Tabs Bar -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-amber-100 space-y-4">
            <!-- Feed Tabs -->
            <div class="flex items-center justify-between border-b border-stone-200 pb-3">
                <div class="flex items-center gap-2">
                    <a href="index.php" 
                       class="px-4 py-2 rounded-xl text-sm font-semibold transition <?= !$viewFavorites ? 'bg-amber-100 text-amber-900' : 'text-stone-600 hover:bg-stone-100' ?>">
                        <i class="fa-solid fa-fire text-amber-700 mr-1.5"></i> All Recipes
                    </a>
                    <?php if ($isLoggedIn): ?>
                        <a href="index.php?view=favorites" 
                           class="px-4 py-2 rounded-xl text-sm font-semibold transition <?= $viewFavorites ? 'bg-amber-100 text-amber-900' : 'text-stone-600 hover:bg-stone-100' ?>">
                            <i class="fa-solid fa-heart text-red-500 mr-1.5"></i> My Favorites
                            <span class="ml-1 text-xs bg-white text-stone-700 px-2 py-0.5 rounded-full border border-stone-200">
                                <?= count($userFavoritedIds) ?>
                            </span>
                        </a>
                    <?php endif; ?>
                </div>

                <span class="text-xs text-stone-400 hidden sm:inline">
                    Showing <?= count($recipes) ?> <?= count($recipes) === 1 ? 'recipe' : 'recipes' ?>
                </span>
            </div>

            <!-- Search & Category Filters -->
            <form id="filter-form" onsubmit="event.preventDefault(); return false;" class="flex flex-col sm:flex-row gap-3 items-center">
                <?php if ($viewFavorites): ?>
                    <input type="hidden" name="view" value="favorites">
                <?php endif; ?>

                <!-- Keyword Search (Expands to fill horizontal space) -->
                <div class="flex-1 w-full">
                    <input type="text" id="live-search" name="search" 
                           value="<?= htmlspecialchars($keyword) ?>" 
                           placeholder="Search recipes by title or ingredients..." 
                           style="border: 1px solid #cbd5e1; background-color: #ffffff; color: #1e293b; padding: 8px 12px; border-radius: 6px; width: 100%;">
                </div>

                <!-- Category Selector -->
                <div class="w-full sm:w-64">
                    <select id="live-category" name="category" style="border: 1px solid #cbd5e1; background-color: #ffffff; color: #1e293b; padding: 8px 12px; border-radius: 6px; width: 100%;">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars((string)$cat['id']) ?>" <?= ($categoryId === (int)$cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Reset Filter -->
                <button type="button" id="clear-filter-btn" title="Reset Filters"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-stone-600 hover:text-stone-900 bg-stone-100 hover:bg-stone-200 rounded-md cursor-pointer transition">
                    Clear
                </button>
            </form>
        </div>

        <!-- No Recipes Found Message -->
        <div id="no-recipes-found" class="<?= empty($recipes) ? '' : 'hidden' ?> bg-white rounded-2xl p-12 text-center border border-amber-100 shadow-sm space-y-3">
            <div class="w-16 h-16 mx-auto rounded-full bg-amber-50 flex items-center justify-center text-amber-700 text-2xl">
                <i class="fa-solid fa-kitchen-set"></i>
            </div>
            <h3 class="text-lg font-bold text-stone-800">No recipes found</h3>
            <p class="text-sm text-stone-500 max-w-sm mx-auto">
                <?= $viewFavorites 
                    ? "You haven't saved any favorite recipes yet. Click the heart icon on any recipe to save it here!" 
                    : "No recipes match your search criteria. Try a different keyword or category!" ?>
            </p>
            <?php if ($isLoggedIn && !$viewFavorites): ?>
                <div class="pt-3">
                    <a href="recipes/create.php" 
                       style="background-color: #d97706; color: #ffffff; padding: 8px 16px; border-radius: 6px; font-weight: bold; text-decoration: none;">
                        Share a Recipe
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recipe Grid / List -->
        <div id="recipes-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 <?= empty($recipes) ? 'hidden' : '' ?>">
            <?php foreach ($recipes as $r): ?>
                <?php 
                $recipeId = (int)$r['id'];
                $isFav    = isset($userFavoritedIds[$recipeId]);
                ?>
                <div class="recipe-card bg-white rounded-2xl border border-amber-100/90 shadow-sm hover:shadow-md transition flex flex-col justify-between overflow-hidden"
                     data-title="<?= htmlspecialchars(mb_strtolower($r['title'])) ?>"
                     data-category="<?= htmlspecialchars((string)$r['category_id']) ?>"
                     data-desc="<?= htmlspecialchars(mb_strtolower($r['description'])) ?>">
                    <div class="p-6 space-y-3">
                        <!-- Badges & Favorite Button -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span style="background-color: #fef3c7; color: #92400e; font-size: 11px; padding: 2px 6px; border-radius: 4px;">
                                    <?= htmlspecialchars($r['category_name']) ?>
                                </span>
                                <?php if ((int)$r['is_edited'] === 1): ?>
                                    <span style="background-color: #fef3c7; color: #92400e; font-size: 11px; padding: 2px 6px; border-radius: 4px;">
                                        (edited)
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Favorite Toggle Button -->
                            <button type="button" 
                                    class="fav-btn p-1.5 rounded-full hover:bg-rose-50 transition cursor-pointer"
                                    data-recipe-id="<?= htmlspecialchars((string)$recipeId) ?>"
                                    title="<?= $isFav ? 'Favorited' : 'Add to Favorites' ?>">
                                <i class="<?= $isFav ? 'fa-solid' : 'fa-regular' ?> fa-heart text-base"
                                   style="color: <?= $isFav ? '#ef4444' : '#94a3b8' ?>;"></i>
                            </button>
                        </div>

                        <!-- Title -->
                        <h2 class="text-xl font-bold text-stone-900 tracking-tight leading-snug hover:text-[#d97706] transition">
                            <a href="recipes/view.php?id=<?= htmlspecialchars((string)$recipeId) ?>">
                                <?= htmlspecialchars($r['title']) ?>
                            </a>
                        </h2>

                        <!-- Author & Time -->
                        <p class="text-xs text-stone-400">
                            By <strong class="text-stone-700"><?= htmlspecialchars($r['username']) ?></strong>
                            &bull; <?= htmlspecialchars(date('M j, Y', strtotime($r['created_at']))) ?>
                        </p>

                        <!-- Description Snippet -->
                        <p class="text-sm text-stone-600 line-clamp-2 leading-relaxed">
                            <?= htmlspecialchars($r['description']) ?>
                        </p>
                    </div>

                    <!-- Card Footer -->
                    <div class="px-6 py-4 bg-stone-50/60 border-t border-stone-100 flex items-center justify-between">
                        <span style="background-color: #fef3c7; color: #92400e; font-size: 11px; padding: 2px 6px; border-radius: 4px;">
                            <i class="fa-regular fa-clock mr-1"></i><?= htmlspecialchars((string)$r['cooking_time_mins']) ?> mins
                        </span>

                        <a href="recipes/view.php?id=<?= htmlspecialchars((string)$recipeId) ?>" 
                           class="text-xs font-semibold text-amber-800 hover:text-amber-950 flex items-center gap-1 transition">
                            <span>View Recipe</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-amber-100 bg-white py-6 text-center text-xs text-stone-500 mt-12">
        Recipe Share &copy; <?= date('Y') ?> &bull; Session-based Authentication &amp; PDO
    </footer>

    <!-- AJAX Favorites Script -->
    <script src="assets/js/favorites.js"></script>

    <!-- Live Search & Category Filter Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterForm = document.getElementById('filter-form');
            const searchInput = document.getElementById('live-search');
            const categorySelect = document.getElementById('live-category');
            const cards = document.querySelectorAll('.recipe-card');
            const noResultsEl = document.getElementById('no-recipes-found');
            const gridEl = document.getElementById('recipes-grid');
            const clearBtn = document.getElementById('clear-filter-btn');

            function applyFilter() {
                const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
                const selectedCat = categorySelect ? categorySelect.value.trim() : '';

                let visibleCount = 0;

                cards.forEach(card => {
                    const title = card.getAttribute('data-title') || '';
                    const category = card.getAttribute('data-category') || '';
                    const desc = card.getAttribute('data-desc') || '';

                    const matchesQuery = !query || title.includes(query) || desc.includes(query);
                    const matchesCat = !selectedCat || category === selectedCat;

                    if (matchesQuery && matchesCat) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (noResultsEl) {
                    if (visibleCount === 0) {
                        noResultsEl.classList.remove('hidden');
                        noResultsEl.style.display = '';
                        if (gridEl) gridEl.classList.add('hidden');
                    } else {
                        noResultsEl.classList.add('hidden');
                        noResultsEl.style.display = 'none';
                        if (gridEl) gridEl.classList.remove('hidden');
                    }
                }
            }

            if (filterForm) {
                filterForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    return false;
                });
            }
            if (searchInput) {
                searchInput.addEventListener('input', applyFilter);
                searchInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                    }
                });
            }
            if (categorySelect) {
                categorySelect.addEventListener('change', applyFilter);
            }
            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    if (searchInput) searchInput.value = '';
                    if (categorySelect) categorySelect.value = '';
                    applyFilter();
                });
            }
        });
    </script>
</body>
</html>
