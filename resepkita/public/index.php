<?php
require_once __DIR__ . '/../src/models/Recipe.php';

// Handle AJAX request
if(isset($_POST['ajax']) && $_POST['ajax'] == 1) {
    $selected = isset($_POST['ingredients']) ? $_POST['ingredients'] : [];
    $search = isset($_POST['search']) ? trim($_POST['search']) : '';
    
    try {
        $recipes = Recipe::findByIngredientsAndName($selected, $search);
        header('Content-Type: application/json');
        $result = [];
        foreach($recipes as $r) {
            $result[] = [
                'id' => $r->getId(),
                'title' => $r->getTitle(),
                'ingredients' => $r->getIngredients(),
                'description' => $r->getDescription(),
            ];
        }
        echo json_encode($result);
        exit;
    } catch (Exception $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

// Regular page load
$mysqli = new mysqli('localhost', 'root', '', 'resepkita');
if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}
$ings = [];
$res = $mysqli->query("SELECT id, name FROM ingredients ORDER BY name ASC");
while ($r = $res->fetch_assoc()) {
    $ings[] = $r;
}
$res->free();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>ResepKita — Pencarian</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <style>
        a{
            text-decoration: none;
            margin-right: 15px;
            color: #333;
        }
    </style>
    <nav>
       <div class="container">
            <div class="brand">
                <a href="index.php"><img src="img/logo.png" class="logo-img" alt="ResepKita"></a>
                <div>
                    <a href="index.php" style="display:block;text-decoration:none;color:inherit;"><strong>ResepKita</strong></a>
                    <div style="font-size:12px;color:#6b7280;">Simpan & Temukan Resep</div>
                </div>
            </div>

            <div class="nav-links">
            <a href="index.php"  class="active">Home</a>
            <a href="add_ingredient.php">Tambah Ingredient</a>
            <a href="add_recipe.php">Tambah Resep</a>
            <a href="manage_ingredients.php">Kelola Bahan</a>
            <a href="manage_recipes.php" >Kelola Resep</a>
    </nav>

    <div class="container">
        <h1>ResepKita</h1>

        <form method="post" action="" id="searchForm">
            <div class="search-bar">
                <input type="text" 
                       id="recipeSearch" 
                       placeholder="Cari resep (contoh: nasi goreng)"
                       onkeyup="searchRecipes()">
            </div>

            <fieldset>
                <legend>Pilih bahan yang tersedia</legend>
                <?php if (empty($ings)): ?>
                    <p>Tidak ada bahan di database.</p>
                <?php else: ?>
                    <div class="ingredients-list">
                        <?php foreach ($ings as $ing): ?>
                            <label>
                                <input type="checkbox" name="ingredients[]" 
                                    value="<?php echo htmlspecialchars($ing['name'], ENT_QUOTES); ?>" 
                                    onchange="searchRecipes()">
                                <?php echo htmlspecialchars($ing['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </fieldset>

            <button type="button" onclick="clearCheckboxes()">Clear Semua Pilihan</button>
        </form>

        <div id="results">
            <h2>Hasil Pencarian</h2>
            <div id="recipesList"></div>
        </div>
    </div>

    <script>
        function searchRecipes() {
            const form = document.getElementById('searchForm');
            const checkedBoxes = form.querySelectorAll('input[type="checkbox"]:checked');
            const selectedIngredients = Array.from(checkedBoxes).map(cb => cb.value);
            const searchQuery = document.getElementById('recipeSearch').value.trim();

            document.getElementById('recipesList').innerHTML = 'Mencari resep...';

            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ajax=1&ingredients=' + encodeURIComponent(JSON.stringify(selectedIngredients)) + 
                      '&search=' + encodeURIComponent(searchQuery)
            })
            .then(response => response.json())
            .then(recipes => {
                const recipesDiv = document.getElementById('recipesList');
                if (recipes.length === 0) {
                    recipesDiv.innerHTML = '<p>Tidak ada resep ditemukan.</p>';
                    return;
                }

                let html = '';
                recipes.forEach(recipe => {
                    html += `
                        <div class="recipe-card">
                            <a href="recipe_detail.php?id=${recipe.id}" style="text-decoration:none;color:inherit;">
                                <div class="recipe-title">${escapeHtml(recipe.title)}</div>
                                <div class="recipe-ingredients">
                                    <strong>Bahan:</strong> ${escapeHtml(recipe.ingredients)}
                                </div>
                                ${recipe.description ? `
                                    <div class="recipe-description" style="font-size:14px;color:#666;margin-top:8px;line-height:1.6;">
                                        ${escapeHtml(recipe.description)}
                                    </div>
                                ` : ''}
                            </a>
                        </div>
                    `;
                });
                recipesDiv.innerHTML = html;
            })
            .catch(error => {
                document.getElementById('recipesList').innerHTML = 
                    '<p class="error">Terjadi kesalahan saat mencari resep.</p>';
                console.error('Error:', error);
            });
        }

        function clearCheckboxes() {
            const checkboxes = document.querySelectorAll('#searchForm input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            searchRecipes();
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        // Initial search on page load
        searchRecipes();
    </script>
</body>
</html>