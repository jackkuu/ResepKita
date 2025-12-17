<?php
$mysqli = new mysqli('localhost', 'root', '', 'resepkita');
if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// Ambil semua resep dengan ingredientsnya
$query = "
    SELECT r.id, r.title, r.description, r.image_path, r.servings
    FROM recipes r
    ORDER BY r.title
";
$result = $mysqli->query($query);
$recipes_raw = [];
while ($row = $result->fetch_assoc()) {
    $recipes_raw[] = $row;
}

// Hitung nutrisi per resep
$recipes = [];
foreach ($recipes_raw as $recipe) {
    $recipe_id = $recipe['id'];
    
    // Ambil ingredients & quantities
    $ing_query = "
        SELECT i.name, ri.quantity, i.calories, i.sugar_g, i.fat_g, i.protein_g, i.carbs_g
        FROM recipe_ingredients ri
        JOIN ingredients i ON ri.ingredient_id = i.id
        WHERE ri.recipe_id = ?
    ";
    $stmt = $mysqli->prepare($ing_query);
    $stmt->bind_param('i', $recipe_id);
    $stmt->execute();
    $ing_result = $stmt->get_result();
    
    $total_calories = 0;
    $total_sugar = 0;
    $total_fat = 0;
    $total_protein = 0;
    $total_carbs = 0;
    $ingredient_count = 0;
    
    while ($ing = $ing_result->fetch_assoc()) {
        if (!$ing['calories']) continue; // Skip if no nutrition data
        
        // Parse quantity to gram (simple parsing)
        $qty = $ing['quantity'] ?? '100g';
        $grams = 100; // default
        
        // Try to extract number from quantity string
        if (preg_match('/(\d+(?:\.\d+)?)\s*(g|gram|gr)?/i', $qty, $m)) {
            $grams = (float)$m[1];
        }
        
        // Calculate nutrition for this ingredient
        $factor = $grams / 100;
        $total_calories += ($ing['calories'] ?? 0) * $factor;
        $total_sugar += ($ing['sugar_g'] ?? 0) * $factor;
        $total_fat += ($ing['fat_g'] ?? 0) * $factor;
        $total_protein += ($ing['protein_g'] ?? 0) * $factor;
        $total_carbs += ($ing['carbs_g'] ?? 0) * $factor;
        $ingredient_count++;
    }
    $stmt->close();
    
    // Calculate health score for recipe
    // Skor = (kalori/10) + (gula x 3) + (lemak x 2) - (protein x 0.5)
    $health_score = ($total_calories / 10) + ($total_sugar * 3) + ($total_fat * 2) - ($total_protein * 0.5);
    
    // Only include recipes with nutrition data
    if ($ingredient_count > 0) {
        $recipe['total_calories'] = round($total_calories, 1);
        $recipe['total_sugar'] = round($total_sugar, 1);
        $recipe['total_fat'] = round($total_fat, 1);
        $recipe['total_protein'] = round($total_protein, 1);
        $recipe['total_carbs'] = round($total_carbs, 1);
        $recipe['health_score'] = round($health_score, 1);
        $recipe['servings'] = $recipe['servings'] ?? 1;
        $recipes[] = $recipe;
    }
}

// Sort by health score (rendah = sehat)
usort($recipes, function($a, $b) {
    return $a['health_score'] <=> $b['health_score'];
});

// Filter based on category
$filter = $_GET['filter'] ?? 'all';
$filtered_recipes = $recipes;

if ($filter === 'sehat') {
    $filtered_recipes = array_filter($recipes, function($r) {
        return $r['health_score'] < 50;
    });
} elseif ($filter === 'sedang') {
    $filtered_recipes = array_filter($recipes, function($r) {
        return $r['health_score'] >= 50 && $r['health_score'] < 100;
    });
} elseif ($filter === 'tinggi') {
    $filtered_recipes = array_filter($recipes, function($r) {
        return $r['health_score'] >= 100;
    });
}

$filtered_recipes = array_values($filtered_recipes);

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Filter Resep Berdasarkan Nilai Gizi — ResepKita</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .nutrition-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 10px 20px;
            border: 2px solid #ddd;
            background: #fff;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
        }
        
        .filter-btn:hover {
            border-color: var(--accent);
            color: var(--accent);
        }
        
        .filter-btn.active {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }
        
        .recipes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .recipe-card {
            background: var(--card);
            border: 1px solid #e0e0e0;
            border-radius: var(--radius);
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
        }
        
        .recipe-card:hover {
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }
        
        .recipe-image {
            width: 100%;
            height: 200px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            overflow: hidden;
        }
        
        .recipe-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .recipe-content {
            padding: var(--pad);
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .recipe-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }
        
        .recipe-description {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 12px;
            flex: 1;
            line-height: 1.5;
        }
        
        .recipe-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            gap: 10px;
        }
        
        .health-score {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .health-score.sehat {
            background: #d4edda;
            color: #155724;
        }
        
        .health-score.sedang {
            background: #fff3cd;
            color: #856404;
        }
        
        .health-score.tidak-sehat {
            background: #f8d7da;
            color: #721c24;
        }
        
        .nutrition-summary {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            font-size: 13px;
            margin-bottom: 12px;
            padding: 8px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        
        .nutrition-item-mini {
            display: flex;
            justify-content: space-between;
        }
        
        .nutrition-label-mini {
            color: #666;
            font-weight: 500;
        }
        
        .nutrition-value-mini {
            color: #333;
            font-weight: bold;
        }
        
        .recipe-link {
            background: var(--accent);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .recipe-link:hover {
            filter: brightness(0.95);
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
            padding: var(--pad);
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        
        .stat-box {
            text-align: center;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: var(--accent);
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }
        
        .no-results {
            padding: 40px;
            text-align: center;
            color: #999;
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
    </style>
</head>
<body>
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
                <a href="index.php">Home</a>
                <a href="filter_recipes_nutrition.php" class="active">Filter Resep</a>
                <a href="nutrition_filter.php">Filter Nutrisi</a>
                <a href="add_ingredient.php">Tambah Ingredient</a>
                <a href="add_recipe.php">Tambah Resep</a>
                <a href="manage_ingredients.php">Kelola Bahan</a>
                <a href="manage_recipes.php">Kelola Resep</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>🍽️ Filter Resep Berdasarkan Nilai Gizi</h1>
        <p style="color: var(--muted); margin-bottom: 20px;">Temukan resep dari yang paling sehat hingga paling berkalori tinggi</p>

        <!-- Filter Buttons -->
        <div class="nutrition-filter">
            <a href="filter_recipes_nutrition.php" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                ✓ Semua Resep (<?php echo count($recipes); ?>)
            </a>
            <a href="filter_recipes_nutrition.php?filter=sehat" class="filter-btn <?php echo $filter === 'sehat' ? 'active' : ''; ?>">
                🟢 Sangat Sehat (<?php echo count(array_filter($recipes, fn($r) => $r['health_score'] < 50)); ?>)
            </a>
            <a href="filter_recipes_nutrition.php?filter=sedang" class="filter-btn <?php echo $filter === 'sedang' ? 'active' : ''; ?>">
                🟡 Sedang (<?php echo count(array_filter($recipes, fn($r) => $r['health_score'] >= 50 && $r['health_score'] < 100)); ?>)
            </a>
            <a href="filter_recipes_nutrition.php?filter=tinggi" class="filter-btn <?php echo $filter === 'tinggi' ? 'active' : ''; ?>">
                🔴 Tinggi Kalori (<?php echo count(array_filter($recipes, fn($r) => $r['health_score'] >= 100)); ?>)
            </a>
        </div>

        <!-- Stats -->
        <div class="stats-container">
            <div class="stat-box">
                <div class="stat-number"><?php echo count($filtered_recipes); ?></div>
                <div class="stat-label">Total Resep</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo round(array_sum(array_column($filtered_recipes, 'total_calories')) / max(count($filtered_recipes), 1), 0); ?></div>
                <div class="stat-label">Rata-rata Kalori</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo round(array_sum(array_column($filtered_recipes, 'total_protein')) / max(count($filtered_recipes), 1), 1); ?>g</div>
                <div class="stat-label">Rata-rata Protein</div>
            </div>
        </div>

        <!-- Recipes Grid -->
        <?php if (count($filtered_recipes) > 0): ?>
            <div class="recipes-grid">
                <?php foreach ($filtered_recipes as $recipe): ?>
                    <?php
                    $category = $recipe['health_score'] < 50 ? 'sehat' : ($recipe['health_score'] < 100 ? 'sedang' : 'tidak-sehat');
                    $category_label = $recipe['health_score'] < 50 ? 'Sangat Sehat' : ($recipe['health_score'] < 100 ? 'Sedang' : 'Tinggi Kalori');
                    ?>
                    <div class="recipe-card">
                        <div class="recipe-image">
                            <?php if (!empty($recipe['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($recipe['image_path']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                            <?php else: ?>
                                Tidak ada foto
                            <?php endif; ?>
                        </div>
                        
                        <div class="recipe-content">
                            <div class="recipe-title"><?php echo htmlspecialchars($recipe['title']); ?></div>
                            
                            <?php if ($recipe['description']): ?>
                                <div class="recipe-description">
                                    <?php echo substr(htmlspecialchars($recipe['description']), 0, 80) . '...'; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="recipe-meta">
                                <span class="health-score <?php echo $category; ?>">
                                    <?php echo $category_label; ?> (<?php echo $recipe['health_score']; ?>)
                                </span>
                            </div>
                            
                            <div class="nutrition-summary">
                                <div class="nutrition-item-mini">
                                    <span class="nutrition-label-mini">🔥 Kalori</span>
                                    <span class="nutrition-value-mini"><?php echo $recipe['total_calories']; ?></span>
                                </div>
                                <div class="nutrition-item-mini">
                                    <span class="nutrition-label-mini">💪 Protein</span>
                                    <span class="nutrition-value-mini"><?php echo $recipe['total_protein']; ?>g</span>
                                </div>
                                <div class="nutrition-item-mini">
                                    <span class="nutrition-label-mini">🧈 Lemak</span>
                                    <span class="nutrition-value-mini"><?php echo $recipe['total_fat']; ?>g</span>
                                </div>
                                <div class="nutrition-item-mini">
                                    <span class="nutrition-label-mini">🌾 Karbo</span>
                                    <span class="nutrition-value-mini"><?php echo $recipe['total_carbs']; ?>g</span>
                                </div>
                            </div>
                            
                            <a href="recipe_detail.php?id=<?php echo $recipe['id']; ?>" class="recipe-link">
                                Lihat Resep Lengkap
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <p style="font-size: 18px;">Tidak ada resep yang sesuai dengan filter ini</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
