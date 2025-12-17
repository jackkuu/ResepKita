<?php
$mysqli = new mysqli('localhost', 'root', '', 'resepkita');
if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// Ambil data ingredients dengan nutrisi
$query = "SELECT * FROM ingredients WHERE calories IS NOT NULL ORDER BY name";
$result = $mysqli->query($query);
$ingredients = [];
while ($row = $result->fetch_assoc()) {
    $ingredients[] = $row;
}

// Hitung skor kesehatan (lebih rendah = lebih sehat)
// Skor = (kalori/100) + (gula*2) + (lemak*1.5) - (protein*0.5) - (serat*0.3)
// Semakin rendah skor = semakin sehat
foreach ($ingredients as &$ing) {
    $calories = $ing['calories'] ?? 0;
    $sugar = $ing['sugar_g'] ?? 0;
    $fat = $ing['fat_g'] ?? 0;
    $protein = $ing['protein_g'] ?? 0;
    
    // Skor kesehatan: kalori + (gula x 3) + (lemak x 2) - (protein x 0.5)
    // Normalisasi agar mudah dibaca
    $health_score = ($calories / 10) + ($sugar * 3) + ($fat * 2) - ($protein * 0.5);
    $ing['health_score'] = round($health_score, 1);
}

// Urutkan berdasarkan skor kesehatan (rendah = sehat, tinggi = tidak sehat)
usort($ingredients, function($a, $b) {
    return $a['health_score'] <=> $b['health_score'];
});

// Filter berdasarkan kategori kesehatan
$filter = $_GET['filter'] ?? 'all';
$filtered_ingredients = $ingredients;

if ($filter === 'sehat') {
    $filtered_ingredients = array_filter($ingredients, function($ing) {
        return $ing['health_score'] < 30;
    });
} elseif ($filter === 'sedang') {
    $filtered_ingredients = array_filter($ingredients, function($ing) {
        return $ing['health_score'] >= 30 && $ing['health_score'] < 60;
    });
} elseif ($filter === 'tinggi') {
    $filtered_ingredients = array_filter($ingredients, function($ing) {
        return $ing['health_score'] >= 60;
    });
}

// Urutkan ulang setelah filter
$filtered_ingredients = array_values($filtered_ingredients);

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Filter Makanan Berdasarkan Nilai Gizi — ResepKita</title>
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
        
        .nutrition-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .ingredient-card {
            background: var(--card);
            border: 1px solid #e0e0e0;
            border-radius: var(--radius);
            padding: var(--pad);
            transition: all 0.3s;
            box-shadow: var(--shadow);
        }
        
        .ingredient-card:hover {
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }
        
        .ingredient-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 12px;
            color: #333;
        }
        
        .health-score {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 12px;
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
        
        .nutrition-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .nutrition-item:last-child {
            border-bottom: none;
        }
        
        .nutrition-label {
            color: #666;
            font-weight: 500;
        }
        
        .nutrition-value {
            color: #333;
            font-weight: bold;
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

                <a href="nutrition_filter.php" class="active">Filter Nutrisi</a>
                <a href="add_ingredient.php">Tambah Ingredient</a>
                <a href="add_recipe.php">Tambah Resep</a>
                <a href="manage_ingredients.php">Kelola Bahan</a>
                <a href="manage_recipes.php">Kelola Resep</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>🥗 Filter Makanan Berdasarkan Nilai Gizi</h1>
        <p style="color: var(--muted); margin-bottom: 20px;">Urutkan bahan makanan dari yang paling sehat hingga paling tidak sehat per 100 gram</p>

        <!-- Filter Buttons -->
        <div class="nutrition-filter">
            <a href="nutrition_filter.php" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                ✓ Semua Bahan (<?php echo count($ingredients); ?>)
            </a>
            <a href="nutrition_filter.php?filter=sehat" class="filter-btn <?php echo $filter === 'sehat' ? 'active' : ''; ?>">
                🟢 Sangat Sehat (<?php echo count(array_filter($ingredients, fn($i) => $i['health_score'] < 30)); ?>)
            </a>
            <a href="nutrition_filter.php?filter=sedang" class="filter-btn <?php echo $filter === 'sedang' ? 'active' : ''; ?>">
                🟡 Sedang (<?php echo count(array_filter($ingredients, fn($i) => $i['health_score'] >= 30 && $i['health_score'] < 60)); ?>)
            </a>
            <a href="nutrition_filter.php?filter=tinggi" class="filter-btn <?php echo $filter === 'tinggi' ? 'active' : ''; ?>">
                🔴 Tinggi Risiko (<?php echo count(array_filter($ingredients, fn($i) => $i['health_score'] >= 60)); ?>)
            </a>
        </div>

        <!-- Stats -->
        <div class="stats-container">
            <div class="stat-box">
                <div class="stat-number"><?php echo count($filtered_ingredients); ?></div>
                <div class="stat-label">Total Bahan</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo round(array_sum(array_column($filtered_ingredients, 'calories')) / max(count($filtered_ingredients), 1), 1); ?></div>
                <div class="stat-label">Rata-rata Kalori</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo round(array_sum(array_column($filtered_ingredients, 'protein_g')) / max(count($filtered_ingredients), 1), 1); ?>g</div>
                <div class="stat-label">Rata-rata Protein</div>
            </div>
        </div>

        <!-- Ingredients Grid -->
        <?php if (count($filtered_ingredients) > 0): ?>
            <div class="nutrition-grid">
                <?php foreach ($filtered_ingredients as $ing): ?>
                    <?php
                    $category = $ing['health_score'] < 30 ? 'sehat' : ($ing['health_score'] < 60 ? 'sedang' : 'tidak-sehat');
                    $category_label = $ing['health_score'] < 30 ? 'Sangat Sehat' : ($ing['health_score'] < 60 ? 'Sedang' : 'Tinggi Risiko');
                    ?>
                    <div class="ingredient-card">
                        <div class="ingredient-name"><?php echo htmlspecialchars($ing['name']); ?></div>
                        <span class="health-score <?php echo $category; ?>">
                            <?php echo $category_label; ?> (<?php echo $ing['health_score']; ?>)
                        </span>
                        
                        <div style="margin-top: 12px;">
                            <div class="nutrition-item">
                                <span class="nutrition-label">🔥 Kalori</span>
                                <span class="nutrition-value"><?php echo $ing['calories']; ?> kcal</span>
                            </div>
                            <div class="nutrition-item">
                                <span class="nutrition-label">🍬 Gula</span>
                                <span class="nutrition-value"><?php echo $ing['sugar_g']; ?> g</span>
                            </div>
                            <div class="nutrition-item">
                                <span class="nutrition-label">🧈 Lemak</span>
                                <span class="nutrition-value"><?php echo $ing['fat_g']; ?> g</span>
                            </div>
                            <div class="nutrition-item">
                                <span class="nutrition-label">💪 Protein</span>
                                <span class="nutrition-value"><?php echo $ing['protein_g']; ?> g</span>
                            </div>
                            <div class="nutrition-item">
                                <span class="nutrition-label">🌾 Karbohidrat</span>
                                <span class="nutrition-value"><?php echo $ing['carbs_g']; ?> g</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <p style="font-size: 18px;">Tidak ada bahan yang sesuai dengan filter ini</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
