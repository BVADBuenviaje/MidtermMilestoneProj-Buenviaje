<?php
require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../classes/Recipe.php';

$recipeModel = new Recipe();
$categories  = $recipeModel->getCategories();
$errors      = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId  = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $cookingTime = isset($_POST['cooking_time_mins']) ? (int)$_POST['cooking_time_mins'] : 0;
    $servings    = isset($_POST['servings']) ? (int)$_POST['servings'] : 0;
    $instructions= trim($_POST['instructions'] ?? '');
    $rawIngredients = $_POST['ingredients'] ?? [];

    $ingredients = [];
    if (is_array($rawIngredients)) {
        foreach ($rawIngredients as $item) {
            $t = trim((string)$item);
            if ($t !== '') {
                $ingredients[] = $t;
            }
        }
    }

    // Validation
    if ($title === '' || strlen($title) < 3) {
        $errors[] = 'Title must be at least 3 characters.';
    }
    if ($description === '') {
        $errors[] = 'Description cannot be empty.';
    }
    if ($categoryId <= 0) {
        $errors[] = 'Please select a valid category.';
    }
    if ($cookingTime <= 0) {
        $errors[] = 'Cooking time must be greater than 0 minutes.';
    }
    if ($servings <= 0) {
        $errors[] = 'Servings must be at least 1.';
    }
    if ($instructions === '') {
        $errors[] = 'Instructions cannot be empty.';
    }
    if (empty($ingredients)) {
        $errors[] = 'Please provide at least one ingredient.';
    }

    if (empty($errors)) {
        try {
            $userId = (int)$_SESSION['user_id'];
            $newRecipeId = $recipeModel->create(
                $userId,
                $categoryId,
                $title,
                $description,
                $instructions,
                $cookingTime,
                $servings,
                $ingredients
            );
            header("Location: ../index.php");
            exit;
        } catch (Exception $e) {
            $errors[] = 'Failed to create recipe: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Recipe - Recipe Share</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/tailwind.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>
<body class="bg-[#fcf9f6] min-h-screen text-stone-800 antialiased p-4 sm:p-8">
    <div class="max-w-3xl mx-auto bg-white p-6 sm:p-10 rounded-2xl shadow-sm border border-amber-100">
        <!-- Header -->
        <div class="flex items-center justify-between pb-6 mb-6 border-b border-stone-200">
            <div>
                <h1 class="text-2xl font-bold text-stone-900">Share a New Recipe</h1>
                <p class="text-sm text-stone-500 mt-1">Fill in the details below to publish your recipe.</p>
            </div>
            <a href="../index.php" class="text-sm text-stone-500 hover:text-stone-800 transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back to Feed
            </a>
        </div>

        <!-- Errors Display -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-6 text-sm space-y-1">
                <?php foreach ($errors as $error): ?>
                    <p class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-red-500 shrink-0"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" action="create.php" class="space-y-6">
            <div>
                <label for="title" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-2">Recipe Title</label>
                <input type="text" id="title" name="title" required
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                       placeholder="e.g. Classic Chicken Adobo"
                       style="border: 1px solid #cbd5e1; background-color: #ffffff; color: #1e293b; padding: 8px; border-radius: 6px; width: 100%;">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="category_id" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-2">Category</label>
                    <select id="category_id" name="category_id" required style="border: 1px solid #cbd5e1; background-color: #ffffff; color: #1e293b; padding: 8px; border-radius: 6px; width: 100%;">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars((string)$cat['id']) ?>" <?= (isset($_POST['category_id']) && (int)$_POST['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="cooking_time_mins" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-2">Cooking Time (mins)</label>
                    <input type="number" id="cooking_time_mins" name="cooking_time_mins" min="1" required
                           value="<?= htmlspecialchars($_POST['cooking_time_mins'] ?? '30') ?>"
                           style="border: 1px solid #cbd5e1; background-color: #ffffff; color: #1e293b; padding: 8px; border-radius: 6px; width: 100%;">
                </div>

                <div>
                    <label for="servings" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-2">Servings</label>
                    <input type="number" id="servings" name="servings" min="1" required
                           value="<?= htmlspecialchars($_POST['servings'] ?? '4') ?>"
                           style="border: 1px solid #cbd5e1; background-color: #ffffff; color: #1e293b; padding: 8px; border-radius: 6px; width: 100%;">
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-2">Short Description</label>
                <textarea id="description" name="description" rows="2" required
                          placeholder="Briefly describe the dish and flavor profile..."
                          style="border: 1px solid #cbd5e1; background-color: #ffffff; color: #1e293b; padding: 8px; border-radius: 6px; width: 100%;"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <!-- Dynamic Ingredients -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-2">Ingredients</label>
                <div id="ingredients-container" class="space-y-2">
                    <?php 
                    $currIngredients = !empty($ingredients) ? $ingredients : [''];
                    foreach ($currIngredients as $idx => $ingVal): 
                    ?>
                        <div class="flex gap-2 items-center ingredient-row">
                            <input type="text" name="ingredients[]" required
                                   value="<?= htmlspecialchars($ingVal) ?>"
                                   placeholder="e.g. 500g Chicken thighs"
                                   style="border: 1px solid #cbd5e1; background-color: #ffffff; color: #1e293b; padding: 8px; border-radius: 6px; width: 100%;">
                            <button type="button" class="remove-ingredient" style="background-color: #fee2e2; color: #b91c1c; padding: 4px 10px; border-radius: 4px; font-weight: 600; border: none; cursor: pointer;">
                                Remove
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="add-ingredient-btn" class="mt-3 text-sm font-semibold text-amber-700 hover:text-amber-800 flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>+ Add Ingredient</span>
                </button>
            </div>

            <div>
                <label for="instructions" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-2">Cooking Instructions</label>
                <textarea id="instructions" name="instructions" rows="6" required
                          placeholder="Step 1: Marinate chicken in soy sauce and vinegar...&#10;Step 2: Heat cooking oil in a pan..."
                          style="border: 1px solid #cbd5e1; background-color: #ffffff; color: #1e293b; padding: 8px; border-radius: 6px; width: 100%;"><?= htmlspecialchars($_POST['instructions'] ?? '') ?></textarea>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-stone-200">
                <button type="submit" style="background-color: #d97706; color: #ffffff; padding: 8px 16px; border-radius: 6px; font-weight: bold; text-decoration: none; border: none; cursor: pointer;">
                    Publish Recipe
                </button>
                <a href="../index.php" class="text-sm text-stone-500 hover:text-stone-800">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        function createIngredientRow(value = '') {
            const row = document.createElement('div');
            row.className = 'flex gap-2 items-center ingredient-row';

            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'ingredients[]';
            input.required = true;
            input.value = value;
            input.placeholder = 'e.g. 2 cloves garlic, minced';
            input.setAttribute('style', 'border: 1px solid #cbd5e1; background-color: #ffffff; color: #1e293b; padding: 8px; border-radius: 6px; width: 100%;');

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.textContent = 'Remove';
            removeBtn.className = 'remove-ingredient';
            removeBtn.setAttribute('style', 'background-color: #fee2e2; color: #b91c1c; padding: 4px 10px; border-radius: 4px; font-weight: 600; border: none; cursor: pointer;');
            removeBtn.addEventListener('click', function() {
                const rows = document.querySelectorAll('.ingredient-row');
                if (rows.length > 1) {
                    row.remove();
                } else {
                    alert('Recipe must contain at least one ingredient.');
                }
            });

            row.appendChild(input);
            row.appendChild(removeBtn);
            return row;
        }

        document.getElementById('add-ingredient-btn').addEventListener('click', function() {
            const container = document.getElementById('ingredients-container');
            const row = createIngredientRow();
            container.appendChild(row);
        });

        document.querySelectorAll('.ingredient-row .remove-ingredient').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const rows = document.querySelectorAll('.ingredient-row');
                if (rows.length > 1) {
                    btn.closest('.ingredient-row').remove();
                } else {
                    alert('Recipe must contain at least one ingredient.');
                }
            });
        });
    </script>
</body>
</html>
