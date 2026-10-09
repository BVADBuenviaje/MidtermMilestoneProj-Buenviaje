/**
 * AJAX Favorite Toggle Handler
 * Recipe Share
 *
 * Audited: No innerHTML or template string injection.
 * Toggles classes, styles, and attributes directly on existing DOM nodes.
 */
document.addEventListener('DOMContentLoaded', () => {
    const attachFavoriteHandlers = () => {
        document.querySelectorAll('.fav-btn').forEach(btn => {
            if (btn.dataset.favBound) return;
            btn.dataset.favBound = 'true';

            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();

                const recipeId = btn.getAttribute('data-recipe-id');
                if (!recipeId) return;

                // Determine relative path to api/toggle_favorite.php
                const apiUrl = window.location.pathname.includes('/recipes/')
                    ? '../api/toggle_favorite.php'
                    : 'api/toggle_favorite.php';

                try {
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ recipe_id: parseInt(recipeId, 10) })
                    });

                    if (response.status === 401) {
                        const loginUrl = window.location.pathname.includes('/recipes/')
                            ? '../auth/login.php'
                            : 'auth/login.php';
                        window.location.href = loginUrl;
                        return;
                    }

                    const data = await response.json();
                    if (data && data.success) {
                        // Directly toggle classes, colors, and attributes on existing icon / SVG element
                        const icon = btn.querySelector('i, svg');
                        if (icon) {
                            if (data.is_favorited) {
                                icon.classList.remove('fa-regular', 'text-stone-400');
                                icon.classList.add('fa-solid', 'text-red-500');
                                icon.style.color = '#ef4444';
                                if (icon.hasAttribute('data-prefix')) {
                                    icon.setAttribute('data-prefix', 'fas');
                                }
                            } else {
                                icon.classList.remove('fa-solid', 'text-red-500');
                                icon.classList.add('fa-regular', 'text-stone-400');
                                icon.style.color = '#94a3b8';
                                if (icon.hasAttribute('data-prefix')) {
                                    icon.setAttribute('data-prefix', 'far');
                                }
                            }
                        }

                        // Direct attribute update on any SVG path if present
                        const path = btn.querySelector('svg path');
                        if (path) {
                            path.setAttribute('fill', data.is_favorited ? '#ef4444' : 'currentColor');
                        }

                        // Direct button attribute updates
                        btn.setAttribute('title', data.is_favorited ? 'Favorited' : 'Add to Favorites');
                        btn.setAttribute('aria-label', data.is_favorited ? 'Favorited' : 'Add to Favorites');

                        // Direct text node assignment
                        const textSpan = btn.querySelector('.fav-text');
                        if (textSpan) {
                            textSpan.textContent = data.is_favorited ? 'Favorited' : 'Favorite';
                        }
                    }
                } catch (error) {
                    console.error('Failed to toggle favorite:', error);
                }
            });
        });
    };

    attachFavoriteHandlers();
});
